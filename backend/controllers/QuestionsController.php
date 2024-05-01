<?php

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Handlers\JwtHandler;
use Stuba\Models\Questions\CreateQuestion\CreateQuestionResponseModel;
use Stuba\Models\Questions\GetAllQuestions\GetQuestionsResponseModel;
use Stuba\Models\Questions\GetQuestion\GetQuestionResponseModel;
use Stuba\Models\Questions\AnswerModel;
use Stuba\Models\Questions\EQuestionType;
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

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }

    #[OA\Get(path: '/api/question', tags: ['Question'])]
    #[OA\Response(response: 200, description: 'Get all questions of logged user', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionsResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getAllQuestionsByUser()
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];
        

        $query = "
        SELECT 
            q.id, 
            q.question AS text, 
            q.active AS active, 
            q.response_type AS type,
            q.subject_id,
            q.creation_date,
            q.author,
            q.question_code AS code,
            s.subject AS subjectName
        FROM Questions q
        LEFT JOIN Subject s ON q.subject_id = s.id
        
    ";

        $stmt = $this->dbConnection->prepare($query);

        $stmt->execute();
        $questionsData = $stmt->fetch(PDO::FETCH_ASSOC);
        $questions = [];
        //var_dump($questionsData);
    
        foreach ($questionsData as $questionData) {
            // Map response type to enum
            switch ($questionData['type']) {
                case 0:
                    $questionType = EQuestionType::SINGLE_CHOICE;
                    break;
                case 1:
                    $questionType = EQuestionType::MULTIPLE_CHOICE;
                    break;
                case 2:
                    $questionType = EQuestionType::TEXT;
                    break;
                default:
                    $questionType = null;
                    break;
            }
            $questions[] = [
                'id' => $questionsData['id'],
                'text' => $questionsData['text'],
                'active' => $questionsData['active'],
                'type' => $questionType,
                'subjectId' => $questionsData['subject_id'],
                'creationDate' => $questionsData['creation_date'],
                'author' => $questionsData['author'],
                'code' => $questionsData['code'],
                'subjectName' => $questionsData['subjectName']
            ];



            if (!$questionsData) {
                // Throw an exception if no question is found
                throw new APIException('Question not found', 404);
            }


            $answersQuery = "SELECT answer as text, correct FROM Answers WHERE question_code = :code";
            $stmt = $this->dbConnection->prepare($answersQuery);
            $stmt->bindParam(':code', $questionsData['code']);
            $stmt->execute();
            $answersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //var_dump($answersData);

            // Map the answers to a format expected by the GetQuestionResponseModel
            $answers = array_map(function ($item) {
                return [
                    'text' => $item['text'],
                    'correct' => $item['correct']
                ];
            }, $answersData);

            var_dump($answers);

            // Include answers in the question data
            $questions['answers'] = $answers;
            var_dump($questions);

            SimpleRouter::response()->json(new GetQuestionResponseModel($questions))->httpCode(200);


        }
    }

    #[OA\Get(path: '/api/question/{code}', description: "Get question by code", tags: ['Question'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question by id of logged user', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getQuestionById(string $code)
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        $deleteAnswersQuery = "Select id FROM Users WHERE username = :username";
        $stmt = $this->dbConnection->prepare($deleteAnswersQuery);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $userid = $stmt->fetchColumn();
        //TODO: Overit ci ma uzivatel pristup k otazke

        //TODO: Na zaklade id vrati otazku

        $questionQuery = "
        SELECT 
            q.question AS text, 
            q.active AS active, 
            q.response_type AS type,
            q.subject_id AS subjectId,
            q.creation_date AS creationDate,
            q.author AS authorId,
            q.question_code AS code,
            s.subject AS subjectName
        FROM Questions q
        LEFT JOIN Subject s ON q.subject_id = s.id
        WHERE q.question_code = :code AND q.author = :username
        ";

        $stmt = $this->dbConnection->prepare($questionQuery);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':username', $userid);
        $stmt->execute();
        $questionData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$questionData) {
            throw new APIException('Unauthorized or Question not found', 401);
        }

    
        $answersQuery = "
            SELECT 
                a.answer AS text, 
                a.correct 
            FROM Answers a
            WHERE a.question_code = :code
        ";
        $stmt = $this->dbConnection->prepare($answersQuery);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $answersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        var_dump($answersData);

        // Prepare answers in the required format
        $answers = array_map(function ($answer) {
            return new AnswerModel([
                'text' => $answer['text'],
                'correct' => $answer['correct']
            ]);
        }, $answersData);

        // Add answers to the question data
        $questionData['answers'] = $answers;

        // Return the complete question details
        $responseModel = new GetQuestionResponseModel($questionData);
        SimpleRouter::response()->json($responseModel)->httpCode(200);


        // Mock data
        /* SimpleRouter::response()->json([
            new GetQuestionResponseModel([
                "text" => "Ako sa Vám páči tento predmet?",
                "active" => true,
                "type" => EQuestionType::SINGLE_CHOICE,
                "subjectId" => 1,
                "creationDate" => "2024-6-17 13:58:32",
                "authorId" => 1,
                "code" => "abcde",
                "answers" => [
                    new AnswerModel([
                        "text" => "Dobrý",
                        "correct" => true
                    ]),
                    new AnswerModel([
                        "text" => "Zlý",
                        "correct" => false
                    ])
                ]
            ])
        ])->httpCode(200); */
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

        $model = new UpdateQuestionRequestModel(SimpleRouter::request()->getInputHandler()->all());

        //TODO: Aktualizovat otazku v databaze
        $this->dbConnection->beginTransaction();

        try {
            // Update the question details
            $updateQuery = "UPDATE Questions SET question = :text, subject_id = :subjectId, active = :active WHERE question_code = :code AND author = :username";
            $stmt = $this->dbConnection->prepare($updateQuery);
            $stmt->bindParam(':text', $model->text);
            $stmt->bindParam(':subjectId', $model->subjectId);
            $stmt->bindParam(':active', $active);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            // Update the answers, you might want to consider deleting existing answers and inserting new ones
            $deleteAnswersQuery = "DELETE FROM Answers WHERE question_code = :code";
            $stmt = $this->dbConnection->prepare($deleteAnswersQuery);
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            foreach ($model->answers as $answer) {
                $insertAnswerQuery = "INSERT INTO Answers (question_code, answer, correct) VALUES (:code, :answer, :correct)";
                $stmt = $this->dbConnection->prepare($insertAnswerQuery);
                $stmt->bindParam(':code', $code);
                $stmt->bindParam(':answer', $answer['text']); 
                $stmt->bindParam(':correct', $correct);
                $stmt->execute();
            }

            // Commit Transaction
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
        $active = $model->active ?? 'N';
        $questionCode = $this->generateQuestionCode();
        //TODO: Vytvorit otazku v databaze
        $this->dbConnection->beginTransaction();

    try {
            $insertQuestionQuery = "INSERT INTO Questions (question, active, response_type, subject_id, author, question_code) VALUES (:question, :active, :type, :subjectId, :authorId, :questionCode)";
        $stmt = $this->dbConnection->prepare($insertQuestionQuery);
        $stmt->bindParam(':question', $model->text);
        $stmt->bindParam(':active', $active);
        $typeValue = $model->type->value; // Získame hodnotu enumu
        $stmt->bindParam(':type', $typeValue);
        $stmt->bindParam(':subjectId', $model->subjectId);
        $stmt->bindParam(':authorId', $model->authorId);
        $stmt->bindParam(':questionCode', $questionCode);
        $stmt->execute();

        
        foreach ($model->answers as $answer) {
            $insertAnswerQuery = "INSERT INTO Answers (question_code, answer, correct) VALUES (:questionCode, :answer, :correct)";
            $stmt = $this->dbConnection->prepare($insertAnswerQuery);
            $stmt->bindParam(':questionCode', $questionCode);
            $stmt->bindParam(':answer', $answer['text']);
            $stmt->bindParam(':correct', $correct);
            $stmt->execute();
        }

        // Commit transaction
        $this->dbConnection->commit();

        SimpleRouter::response()->json(new CreateQuestionResponseModel(['code' => $questionCode]))->httpCode(200);
    } catch (\Exception $e) {
        // Rollback on error
        $this->dbConnection->rollback();
        throw new APIException('Failed to create question: ' . $e->getMessage(), 500);
    }


        //Mock data
        /* SimpleRouter::response()->json([
            new CreateQuestionResponseModel([
                "code" => "abcde",
            ])
        ])->httpCode(200); */
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
        $deleteAnswersQuery = "Select id FROM Users WHERE username = :username";
        $stmt = $this->dbConnection->prepare($deleteAnswersQuery);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $userid = $stmt->fetchColumn();

        $this->dbConnection->beginTransaction();

    

    try {
        
        // Delete answers associated with the question
        $deleteAnswersQuery = "DELETE FROM Answers WHERE question_code = :code";
        $stmt = $this->dbConnection->prepare($deleteAnswersQuery);
        $stmt->bindParam(':code', $code);
        $stmt->execute(); 

        // Delete the question      //TODO: username                 
        $deleteQuestionQuery = "DELETE FROM Questions WHERE question_code = :code AND author = :username";
        $stmt = $this->dbConnection->prepare($deleteQuestionQuery);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':username', $userid);
        $stmt->execute();

        // Commit transaction
        $this->dbConnection->commit();
        SimpleRouter::response()->httpCode(200);
    } catch (\Exception $e) {
        // Rollback on error
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