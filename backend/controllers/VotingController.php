<?php declare(strict_types=1);

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Stuba\Db\DbAccess;
use Pecee\SimpleRouter\SimpleRouter;
use PDO;
use Stuba\Exceptions\APIException;
use Stuba\Handlers\Jwt\JwtHandler;
use Stuba\Models\Voting\GetQuestionWithAnswers\GetQuestionWithAnswersResponseModel;
use Stuba\Models\Voting\GetCorrectAnswerId\GetCorrectAnswerIdResponseModel;
use Stuba\Models\Voting\GetQuestionStatistics\GetQuestionStatisticsResponseModel;
use Stuba\Models\Voting\VoteByCode\VoteByCodeRequestModel;

#[OA\Tag('Voting')]
class VotingController
{
    private PDO $dbConnection;
    private JwtHandler $jwtHandler;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }

    #[OA\Get(path: '/api/Voting/{code}', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question with answers', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionWithAnswersResponseModel'))]
    #[OA\Response(response: 404, description: 'Question not found')]
    public function getQuestionWithAnswersByCode(string $code)
    {
        $stmt = $this->dbConnection->prepare("SELECT question, response_type AS type, active FROM Questions WHERE question_code = :code");
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $questionData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$questionData) {
            SimpleRouter::response()->json(['error' => 'Question not found'])->httpCode(404);
            return;
        }

        // Fetch the answers without indicating the correct one
        $stmt = $this->dbConnection->prepare("SELECT answer FROM Answers WHERE question_code = :code");
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $answersArray = array_map(function ($item) {
            return $item['answer']; // Only return the answer text
        }, $answers);

        // Construct the response model
        $responseModel = new GetQuestionWithAnswersResponseModel();
        $responseModel->question = $questionData['question'];
        $responseModel->type = $questionData['type'];
        $responseModel->active = $questionData['active'];
        $responseModel->answers = $answersArray;

        // Return the response
        SimpleRouter::response()->json($responseModel)->httpCode(200);
    }

    #[OA\Post(path: '/api/Voting/{code}', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(description: 'Vote by code', required: true, content: new OA\JsonContent(ref: '#/components/schemas/VoteByCodeRequestModel'))]
    #[OA\Response(response: 200, description: 'Vote successful')]
    #[OA\Response(response: 401, description: 'Already voted')]
    #[OA\Response(response: 404, description: 'Question not found')]

    public function voteByCode(string $code)
    {
        $query = "SELECT id FROM Voting WHERE question_code = :code";
        $stmt = $this->dbConnection->prepare($query);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            SimpleRouter::response()->json(['error' => 'Question not found'])->httpCode(404);
            return;
        }

        $VotingId = $stmt->fetchColumn();
        $data = SimpleRouter::request()->getInputHandler()->all();
        $answerId = $data['answerId'] ?? null;
        $answerText = $data['answerText'] ?? null;

        if ($answerId === null && $answerText === null) {
            SimpleRouter::response()->json(['error' => 'No answer provided'])->httpCode(400);
            return;
        }

        // Decide which field and parameter to use based on provided data
        $field = 'answer';
        if ($answerId !== null) {
            $param = $answerId;
        } else {
            $param = $answerText;
        }

        // Prepare and execute the insertion query
        $insertQuery = "INSERT INTO Vote (Voting_id, $field) VALUES (:VotingId, :param)";
        $insertStmt = $this->dbConnection->prepare($insertQuery);
        $insertStmt->bindParam(':VotingId', $VotingId);
        $insertStmt->bindParam(':param', $param);
        $insertStmt->execute();

        SimpleRouter::response()->json(['message' => 'Vote recorded successfully'])->httpCode(200);
    }

    #[OA\Get(path: '/api/Voting/{code}/correct', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get correct answer id', content: new OA\JsonContent(ref: '#/components/schemas/GetCorrectAnswerIdResponseModel'))]
    #[OA\Response(response: 401, description: 'Did not vote yet')]
    #[OA\Response(response: 400, description: 'Question does not have correct answer')]
    #[OA\Response(response: 404, description: 'Question not found')]

    public function getCorrectAnswerId(string $code)
    {
        // Fetch the correct answer ID based on the question code
        $stmt = $this->dbConnection->prepare("SELECT id FROM Answers WHERE question_code = :code AND correct = 1");
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $answer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$answer) {
            SimpleRouter::response()->json(['error' => 'Correct answer not found or question does not exist'])->httpCode(404);
            return;
        }

        // Prepare the response model with the fetched answer ID
        $responseModel = new GetCorrectAnswerIdResponseModel();
        $responseModel->answerId = (int) $answer['id'];

        // Return the response
        SimpleRouter::response()->json($responseModel)->httpCode(200);


    }

    #[OA\Get(path: '/api/Voting/{code}/statistics', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question statistics', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionStatisticsResponseModel'))]
    #[OA\Response(response: 401, description: 'Did not vote yet')]
    #[OA\Response(response: 404, description: 'Question not found')]
    public function getQuestionStatistics(string $code)
    {

        $query = "SELECT a.id AS answerId, a.answer AS questionText, COUNT(v.id) AS count
                  FROM Answers a
                  LEFT JOIN Votes v ON a.id = v.answer_id
                  WHERE a.question_code = :code
                  GROUP BY a.id";
        $stmt = $this->dbConnection->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$results) {
            SimpleRouter::response()->json(['error' => 'No data found for this question'])->httpCode(404);
            return;
        }

        // Map results to response models
        $statistics = array_map(function ($item) {
            return GetQuestionStatisticsResponseModel::constructFromModel($item);
        }, $results);

        // Return the response
        SimpleRouter::response()->json($statistics)->httpCode(200);
    }
}