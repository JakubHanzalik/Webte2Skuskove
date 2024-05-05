<?php

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Handlers\Jwt\JwtHandler;
use Stuba\Handlers\User\GetUserByUsernameHandler;
use Stuba\Models\Questions\CreateQuestion\CreateQuestionResponseModel;
use Stuba\Models\Questions\GetAllQuestions\GetQuestionsResponseModel;
use Stuba\Models\Questions\GetQuestion\GetQuestionResponseModel;
use Stuba\Models\Questions\AnswerModel;
use Stuba\Models\Questions\UpdateQuestion\UpdateQuestionRequestModel;
use Stuba\Models\Questions\CreateQuestion\CreateQuestionRequestModel;
use PDO;
use Stuba\Db\DbAccess;
use Stuba\Exceptions\APIException;

#[OA\Tag('Question')]
class QuestionsController
{
    private JwtHandler $jwtHandler;
    private PDO $dbConnection;

    private GetUserByUsernameHandler $userHandler;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
        $this->dbConnection = (new DbAccess())->getDbConnection();
        $this->userHandler = new GetUserByUsernameHandler();
    }

    #[OA\Get(path: '/api/question', tags: ['Question'])]
    #[OA\Response(response: 200, description: "Get all questions of logged user", content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'array', items: new OA\Items(ref: '#/components/schemas/GetQuestionsResponseModel'))))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getAllQuestionsByUser()
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        $user = $this->userHandler->handle($username);

        if ($user == null)
            throw new APIException("User does not exists", 500);

        $query =
            "SELECT 
                q.question AS text, 
                q.active AS active, 
                q.subject_id AS subjectId,
                q.question_code AS code
            FROM Questions q WHERE q.author_id = :authorId";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(":authorId", $user->id);

        $statement->execute();
        $response = $statement->fetchAll(PDO::FETCH_CLASS, GetQuestionsResponseModel::class);

        SimpleRouter::response()->json($response)->httpCode(200);
    }

    #[OA\Get(path: '/api/question/{code}', description: "Get question by code", tags: ['Question'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question by id of logged user', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getQuestionByCode(string $code)
    {
        $questionQuery =
            "SELECT 
            q.question AS text, 
            q.active AS active, 
            q.response_type AS type,
            q.subject_id AS subjectId,
            q.creation_date AS creationDate,
            q.author_id AS authorId,
            q.question_code AS code
        FROM Questions q
        WHERE q.question_code = :code";

        $stmt = $this->dbConnection->prepare($questionQuery);
        $stmt->bindParam(':code', $code);
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, GetQuestionResponseModel::class);
        $stmt->execute();
        $question = $stmt->fetch();

        $answersQuery =
            "SELECT 
            a.id AS id,
            a.answer AS text, 
            a.correct 
        FROM Answers a
        WHERE a.question_code = :code";

        $stmt = $this->dbConnection->prepare($answersQuery);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $answers = $stmt->fetchAll(PDO::FETCH_CLASS, AnswerModel::class);

        $question->answers = $answers;

        SimpleRouter::response()->json($question)->httpCode(200);
    }

    #[OA\Post(path: '/api/question/{code}', tags: ['Question'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(description: 'Update question', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateQuestionRequestModel'))]
    #[OA\Response(response: 200, description: 'Update question')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function updateQuestion(string $code)
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        $user = $this->userHandler->handle($username);

        $model = new UpdateQuestionRequestModel(SimpleRouter::request()->getInputHandler()->all());

        if (!$model->isValid()) {
            SimpleRouter::response()->json($model->getErrors())->httpCode(400);
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
                $insertAnswerStmt->bindValue(':answer', $model->answers[$i]->text, PDO::PARAM_STR);
                $insertAnswerStmt->bindValue(':correct', $model->answers[$i]->correct, PDO::PARAM_BOOL);
                $insertAnswerStmt->bindValue(':id', $i, PDO::PARAM_INT);
                $insertAnswerStmt->execute();
            }

            $this->dbConnection->commit();
            SimpleRouter::response()->httpCode(200);
        } catch (APIException $e) {
            // Rollback Transaction on Error
            $this->dbConnection->rollback();
            throw new APIException('Failed to update question: ' . $e->getMessage(), 500);
        }

    }

    #[OA\Put(path: '/api/question', tags: ['Question'])]
    #[OA\RequestBody(description: 'Create question', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateQuestionRequestModel'))]
    #[OA\Response(response: 200, description: 'Create question', content: new OA\JsonContent(ref: '#/components/schemas/CreateQuestionResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function createQuestion()
    {
        $model = new CreateQuestionRequestModel(SimpleRouter::request()->getInputHandler()->all());
        if (!$model->isValid()) {
            SimpleRouter::response()->json($model->getErrors())->httpCode(400);
        }

        $this->dbConnection->beginTransaction();

        try {
            $questionCode = $this->generateQuestionCode();

            $insertQuestionQuery = "INSERT INTO Questions (question, active, response_type, subject_id, author_id, question_code) VALUES (:question, :active, :type, :subjectId, :authorId, :questionCode)";
            $insertQuestionStmt = $this->dbConnection->prepare($insertQuestionQuery);
            $insertQuestionStmt->bindValue(':question', $model->text, PDO::PARAM_STR);
            $insertQuestionStmt->bindValue(':active', $model->active, PDO::PARAM_BOOL);
            $insertQuestionStmt->bindValue(':type', $model->type->value, PDO::PARAM_INT);
            $insertQuestionStmt->bindValue(':subjectId', $model->subjectId, PDO::PARAM_INT);
            $insertQuestionStmt->bindValue(':authorId', $model->authorId, PDO::PARAM_INT);
            $insertQuestionStmt->bindValue(':questionCode', $questionCode, PDO::PARAM_STR);
            $insertQuestionStmt->execute();

            if ($model->active) {
                $insertVotingQuery = "INSERT INTO Voting (question_code, date_from) VALUES (:questionCode, CURDATE())";
                $insertVotingStmt = $this->dbConnection->prepare($insertVotingQuery);
                $insertVotingStmt->bindValue(':questionCode', $questionCode, PDO::PARAM_STR);
                $insertVotingStmt->execute();
            }else{
                $insertVotingQuery = "INSERT INTO Voting (question_code) VALUES (:questionCode)";
                $insertVotingStmt = $this->dbConnection->prepare($insertVotingQuery);
                $insertVotingStmt->bindValue(':questionCode', $questionCode, PDO::PARAM_STR);
                $insertVotingStmt->execute();
            }

            for ($i = 0; $i < count($model->answers); $i++) {
                $insertAnswerQuery = "INSERT INTO Answers (id, question_code, answer, correct) VALUES (:id, :code, :answer, :correct)";
                $insertAnswerStmt = $this->dbConnection->prepare($insertAnswerQuery);
                $insertAnswerStmt->bindValue(':code', $questionCode, PDO::PARAM_STR);
                $insertAnswerStmt->bindValue(':answer', $model->answers[$i]->text, PDO::PARAM_STR);
                $insertAnswerStmt->bindValue(':correct', $model->answers[$i]->correct, PDO::PARAM_BOOL);
                $insertAnswerStmt->bindValue(':id', $i, PDO::PARAM_INT);
                $insertAnswerStmt->execute();
            }

            $this->dbConnection->commit();

            SimpleRouter::response()->json(CreateQuestionResponseModel::constructFromModel(['code' => $questionCode]))->httpCode(200);
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

        $user = $this->userHandler->handle($username);

        $this->dbConnection->beginTransaction();

        try {
            $deleteAnswersQuery = "DELETE FROM Answers WHERE question_code = :code";
            $stmt = $this->dbConnection->prepare($deleteAnswersQuery);
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            $deleteQuestionQuery = "DELETE FROM Questions WHERE question_code = :code AND author_id = :userId";
            $stmt = $this->dbConnection->prepare($deleteQuestionQuery);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':userId', $user->id);
            $stmt->execute();

            $this->dbConnection->commit();
            SimpleRouter::response()->httpCode(200);
        } catch (\Exception $e) {
            $this->dbConnection->rollback();
            throw new APIException('Failed to delete question: ' . $e->getMessage(), 500);
        }
    }

    function generateQuestionCode($length = 5)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}