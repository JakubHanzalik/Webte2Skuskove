<?php

namespace Stuba\Controllers;

use PDO;
use Stuba\Db\DbAccess;
use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Exceptions\APIException;
use Stuba\Handlers\Jwt\JwtHandler;
use Stuba\Handlers\Answer\GetAnswersByQuestionCodeHandler;
use Stuba\Handlers\User\GetUserByUsernameHandler;
use Stuba\Handlers\Question\GetQuestionByCodeHandler;
use Stuba\Handlers\Voting\CreateVotingByQuestionCodeHandler;
use Stuba\Models\Questions\CreateQuestion\CreateQuestionResponseModel;
use Stuba\Models\Questions\ExportQuestion\ExportAnswerModel;
use Stuba\Models\Questions\ExportQuestion\ExportQuestionModel;
use Stuba\Models\Questions\ExportQuestion\ExportVoteModel;
use Stuba\Models\Questions\ExportQuestion\ExportVotingModel;
use Stuba\Models\Questions\GetAllQuestions\GetQuestionsResponseModel;
use Stuba\Models\Questions\GetQuestion\GetQuestionAnswerResponseModel;
use Stuba\Models\Questions\UpdateQuestion\UpdateQuestionRequestModel;
use Stuba\Models\Questions\CreateQuestion\CreateQuestionRequestModel;
use Stuba\Models\Questions\GetQuestion\GetQuestionResponseModel;
use Stuba\Db\Models\User\EUserRole;

#[OA\Tag('Question', description: "Endpointy pre spravu otazok prihlaseneho pouzivatela")]
class QuestionsController
{
    private JwtHandler $jwtHandler;
    private PDO $dbConnection;
    private GetUserByUsernameHandler $getUserByUsernameHandler;
    private GetQuestionByCodeHandler $getQuestionByCodeHandler;
    private GetAnswersByQuestionCodeHandler $getAnswersByQuestionCodeHandler;
    private CreateVotingByQuestionCodeHandler $createVotingByQuestionCodeHandler;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
        $this->dbConnection = (new DbAccess())->getDbConnection();
        $this->getUserByUsernameHandler = new GetUserByUsernameHandler();
        $this->getQuestionByCodeHandler = new GetQuestionByCodeHandler();
        $this->getAnswersByQuestionCodeHandler = new GetAnswersByQuestionCodeHandler();
        $this->createVotingByQuestionCodeHandler = new CreateVotingByQuestionCodeHandler();
    }

    #[OA\Get(path: '/api/question', tags: ['Question'])]
    #[OA\Response(response: 200, description: "Get all questions of logged user", content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'array', items: new OA\Items(ref: '#/components/schemas/GetQuestionsResponseModel'))))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 500, description: 'User does not exists')]
    public function getAllQuestionsByUser()
    {
        try {
            $accessToken = $_COOKIE["AccessToken"];
            $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

            $user = $this->getUserByUsernameHandler->handle($username);

            if (is_null($user)) {
                throw new APIException("User does not exists", 500);
            }

            $query =
                "SELECT 
                q.question AS text, 
                q.active AS active, 
                q.subject_id AS subjectId,
                q.question_code AS code,
                q.creation_date AS creationDate
            FROM Questions q WHERE q.author_id = :authorId";
            $statement = $this->dbConnection->prepare($query);
            $statement->bindParam(":authorId", $user->id);

            $statement->execute();

            $response = $statement->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, GetQuestionsResponseModel::class);

            SimpleRouter::response()->httpCode(200);
            SimpleRouter::response()->json($response);
        } catch (\PDOException $e) {
            SimpleRouter::response()->httpCode(500);
            SimpleRouter::response()->json(['error' => 'Database error: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            SimpleRouter::response()->httpCode(500);
            SimpleRouter::response()->json(['error' => 'Internal Server Error']);
        }
    }

    #[OA\Get(path: '/api/question/export', tags: ['Question'])]
    #[OA\Response(response: 200, description: "Export all questions of logged user", content: new OA\MediaType(mediaType: 'application/json', ))]
    public function exportQuestionsByUser()
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        $user = $this->getUserByUsernameHandler->handle($username);

        if (is_null($user)) {
            throw new APIException("User does not exists", 500);
        }

        $getQuestionsQuery =
            "SELECT 
                q.question_code AS code,
                q.question AS text, 
                q.response_type AS type,
                s.text AS subject,
                q.creation_date AS creationDate,
                u.username AS author,
                q.active AS isActive
                
            FROM Questions q 
            JOIN Subject s ON q.subject_id = s.id
            JOIN Users u ON q.author_id = u.id
            WHERE q.author_id = :authorId";
        $questionsStmt = $this->dbConnection->prepare($getQuestionsQuery);
        $questionsStmt->bindParam(":authorId", $user->id);

        $questionsStmt->execute();

        $questions = $questionsStmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, ExportQuestionModel::class);

        foreach ($questions as $question) {
            $getAnswersQuery =
                "SELECT id,
                    answer AS text,
                    correct AS isCorrect
                FROM Answers WHERE question_code = :code";
            $answersStmt = $this->dbConnection->prepare($getAnswersQuery);
            $answersStmt->bindParam(":code", $question->code);
            $answersStmt->execute();

            $answers = $answersStmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, ExportAnswerModel::class);
            $question->answers = $answers;

            $getVotingsQuery =
                "SELECT date_from AS startDate,
                    id,
                    date_to AS endDate,
                    note
                FROM Voting v WHERE v.question_code = :code";

            $votingsStmt = $this->dbConnection->prepare($getVotingsQuery);
            $votingsStmt->bindParam(":code", $question->code);
            $votingsStmt->execute();

            $votings = $votingsStmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, ExportVotingModel::class);
            $question->votings = $votings;

            foreach ($votings as $voting) {
                $getIdVotesQuery =
                    "SELECT a.answer AS answerText,
                        COUNT(v.id) AS count
                    FROM Vote v
                    JOIN Answers a ON v.answer_id = a.id AND a.question_code = :code
                    WHERE v.voting_id = :votingId
                    GROUP BY a.id";

                $getIdVotesStmt = $this->dbConnection->prepare($getIdVotesQuery);
                $getIdVotesStmt->bindParam(":votingId", $voting->id);
                $getIdVotesStmt->bindParam(":code", $question->code);
                $getIdVotesStmt->execute();

                $votes = $getIdVotesStmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, ExportVoteModel::class);
                $voting->votes = $votes;

                $getTextVotesQuery =
                    "SELECT v.answer_text AS answerText,
                        COUNT(v.id) AS count
                    FROM Vote v
                    WHERE v.voting_id = :votingId AND v.answer_text IS NOT NULL
                    GROUP BY v.answer_text";

                $getTextVotesStmt = $this->dbConnection->prepare($getTextVotesQuery);
                $getTextVotesStmt->bindParam(":votingId", $voting->id);
                $getTextVotesStmt->execute();

                $votes = $getTextVotesStmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, ExportVoteModel::class);
                $voting->votes = array_merge($voting->votes, $votes);
            }
        }

        $jsonData = json_encode($questions, JSON_PRETTY_PRINT);

        SimpleRouter::response()->httpCode(200);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="export.json"');
        header('Content-Length: ' . strlen($jsonData));
        echo $jsonData;
    }

    #[OA\Get(path: '/api/question/{code}', description: "Get question by code", tags: ['Question'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question by id of logged user', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'Question not found')]
    public function getQuestionByCode(string $code)
    {
        $question = $this->getQuestionByCodeHandler->handle($code);

        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        $user = $this->getUserByUsernameHandler->handle($username);

        if ($question->author_id != $user->id) {
            throw new APIException("User is not authorized to view question", 401);
        }

        if (is_null($question)) {
            throw new APIException("Question not found", 404);
        }

        $answers = $this->getAnswersByQuestionCodeHandler->handle($code);

        $answersResponse = [];

        if (!is_null($answers)) {
            foreach ($answers as $answer) {
                array_push($answersResponse, GetQuestionAnswerResponseModel::constructFromModel([
                    'id' => $answer->id,
                    'answer' => $answer->answer,
                    'correct' => $answer->correct
                ]));
            }
        }

        $response = GetQuestionResponseModel::constructFromModel([
            'text' => $question->question,
            'active' => $question->active,
            'type' => $question->response_type->value,
            'subjectId' => $question->subject_id,
            'creationDate' => $question->creation_date,
            'code' => $question->question_code,
            'answers' => $answersResponse
        ]);

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json($response);
    }

    #[OA\Put(path: '/api/question/{code}', tags: ['Question'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(description: 'Update question', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateQuestionRequestModel'))]
    #[OA\Response(response: 200, description: 'Update question')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 400, description: 'Invalid input')]
    public function updateQuestion(string $code)
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        $user = $this->getUserByUsernameHandler->handle($username);

        $model = new UpdateQuestionRequestModel(SimpleRouter::request()->getInputHandler()->all());

        $question = $this->getQuestionByCodeHandler->handle($code);

        if ($question->author_id != $user->id) {
            throw new APIException("User is not authorized to update question", 401);
        }
        $this->dbConnection->beginTransaction();

        try {
            $updateQuestionsQuery = "UPDATE Questions SET question = :text, subject_id = :subjectId, active = :active WHERE question_code = :code AND author_id = :userId";
            $questionsStmt = $this->dbConnection->prepare($updateQuestionsQuery);
            $questionsStmt->bindValue(':text', $model->text, PDO::PARAM_STR);
            $questionsStmt->bindValue(':subjectId', $model->subjectId, PDO::PARAM_STR);
            $questionsStmt->bindValue(':active', $model->active, PDO::PARAM_BOOL);
            $questionsStmt->bindParam(':code', $code);
            $questionsStmt->bindParam(':userId', $user->id);
            $questionsStmt->execute();

            $deleteAnswersQuery = "DELETE FROM Answers WHERE question_code = :code";
            $deleteAnswersStmt = $this->dbConnection->prepare($deleteAnswersQuery);
            $deleteAnswersStmt->bindParam(':code', $code);
            $deleteAnswersStmt->execute();

            for ($i = 0; $i < count($model->answers); $i++) {
                $insertAnswerQuery = "INSERT INTO Answers (id, question_code, answer, correct) VALUES (:id, :code, :answer, :correct)";
                $insertAnswerStmt = $this->dbConnection->prepare($insertAnswerQuery);
                $insertAnswerStmt->bindValue(':code', $code, PDO::PARAM_STR);
                $insertAnswerStmt->bindValue(':answer', $model->answers[$i]->answer, PDO::PARAM_STR);
                $insertAnswerStmt->bindValue(':correct', $model->answers[$i]->correct, PDO::PARAM_BOOL);
                $insertAnswerStmt->bindValue(':id', $i, PDO::PARAM_INT);
                $insertAnswerStmt->execute();
            }

            $this->dbConnection->commit();
            SimpleRouter::response()->httpCode(200);
            SimpleRouter::response()->json(['message' => 'Question updated successfully']);
        } catch (APIException $e) {
            $this->dbConnection->rollback();
        }
    }

    #[OA\Post(path: '/api/question', tags: ['Question'])]
    #[OA\RequestBody(description: 'Create question', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateQuestionRequestModel'))]
    #[OA\Response(response: 200, description: 'Create question', content: new OA\JsonContent(ref: '#/components/schemas/CreateQuestionResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 400, description: 'Invalid input')]
    public function createQuestion()
    {
        $model = new CreateQuestionRequestModel(SimpleRouter::request()->getInputHandler()->all());

        $accessToken = $_COOKIE["AccessToken"];
        $decoded = $this->jwtHandler->decodeAccessToken($accessToken);
        $user = $this->getUserByUsernameHandler->handle($decoded["sub"]);

        $this->dbConnection->beginTransaction();

        try {
            $questionCode = $this->generateQuestionCode();

            $insertQuestionQuery = "INSERT INTO Questions (question_code, active, response_type, subject_id, author_id, question) VALUES (:questionCode, :active, :type, :subjectId, :authorId, :question)";
            $insertQuestionStmt = $this->dbConnection->prepare($insertQuestionQuery);
            $insertQuestionStmt->bindValue(':question', $model->text, PDO::PARAM_STR);
            $insertQuestionStmt->bindValue(':active', $model->active, PDO::PARAM_BOOL);
            $insertQuestionStmt->bindValue(':type', $model->type->value, PDO::PARAM_INT);
            $insertQuestionStmt->bindValue(':subjectId', $model->subjectId, PDO::PARAM_INT);
            $insertQuestionStmt->bindValue(':authorId', $user->id, PDO::PARAM_INT);
            $insertQuestionStmt->bindValue(':questionCode', $questionCode, PDO::PARAM_STR);
            $insertQuestionStmt->execute();



            for ($i = 0; $i < count($model->answers); $i++) {
                $insertAnswerQuery = "INSERT INTO Answers (id, question_code, answer, correct) VALUES (:id, :code, :answer, :correct)";
                $insertAnswerStmt = $this->dbConnection->prepare($insertAnswerQuery);
                $insertAnswerStmt->bindValue(':code', $questionCode, PDO::PARAM_STR);
                $insertAnswerStmt->bindValue(':answer', $model->answers[$i]->answer, PDO::PARAM_STR);
                $insertAnswerStmt->bindValue(':correct', $model->answers[$i]->correct, PDO::PARAM_BOOL);
                $insertAnswerStmt->bindValue(':id', $i, PDO::PARAM_INT);
                $insertAnswerStmt->execute();
            }

            $this->dbConnection->commit();

            if ($model->active) {
                $this->createVotingByQuestionCodeHandler->handle($questionCode);
            }

            SimpleRouter::response()->httpCode(200);
            SimpleRouter::response()->json(CreateQuestionResponseModel::constructFromModel([
                'code' => $questionCode
            ]));
        } catch (\Exception $e) {
            $this->dbConnection->rollback();
            throw new APIException('Failed to create question: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(path: '/api/question/{code}', tags: ['Question'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Delete question')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 500, description: 'Failed to delete question')]
    public function deleteQuestion(string $code)
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        $user = $this->getUserByUsernameHandler->handle($username);

        $getQuestionAuthorQuery = "SELECT author_id FROM Questions WHERE question_code = :code";
        $stmt = $this->dbConnection->prepare($getQuestionAuthorQuery);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $authorId = $stmt->fetchColumn();

        if ($authorId != $user->id && $user->role != EUserRole::ADMIN)
            throw new APIException("User is not authorized to delete question", 401);

        $this->dbConnection->beginTransaction();

        try {
            $deleteAnswersQuery = "DELETE FROM Answers WHERE question_code = :code";
            $stmt = $this->dbConnection->prepare($deleteAnswersQuery);
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            $deleteQuestionQuery = "DELETE FROM Questions WHERE question_code = :code";
            $stmt = $this->dbConnection->prepare($deleteQuestionQuery);
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            $this->dbConnection->commit();
            SimpleRouter::response()->httpCode(200);
            SimpleRouter::response()->json(['message' => 'Question deleted successfully']);
        } catch (\Exception $e) {
            $this->dbConnection->rollback();
            throw new APIException('Failed to delete question: ' . $e->getMessage(), 500);
        }
    }

    private function generateQuestionCode($length = 5)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        do {
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
        } while ($this->doesCodeExists($randomString));

        return $randomString;
    }

    private function doesCodeExists(string $code)
    {
        $query = "SELECT COUNT(*) FROM Questions WHERE question_code = :code";
        $stmt = $this->dbConnection->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
