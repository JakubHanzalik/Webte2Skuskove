<?php

declare(strict_types=1);

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
use Stuba\Models\Voting\GetQuestionWithAnswers\GetQuestionAnswerModel;
use Stuba\Models\Voting\VoteByCode\VoteByCodeRequestModel;
use Stuba\Handlers\Question\GetQuestionByCodeHandler;
use Stuba\Models\Questions\EQuestionType;

#[OA\Tag('Voting')]
class VotingController
{
    private PDO $dbConnection;
    private GetQuestionByCodeHandler $getQuestionByCodeHandler;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
        $this->getQuestionByCodeHandler = new GetQuestionByCodeHandler();
    }

    #[OA\Get(path: '/api/voting/{code}', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question with answers', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionWithAnswersResponseModel'))]
    #[OA\Response(response: 404, description: 'Question not found')]
    public function getQuestionWithAnswersByCode(string $code)
    {
        $question = $this->getQuestionByCodeHandler->handle($code);

        if (is_null($question)) {
            throw new APIException('Question not found', 404);
        }

        $response = GetQuestionWithAnswersResponseModel::constructFromModel([
            'question' => $question->question,
            'type' => $question->response_type->value
        ]);

        $stmt = $this->dbConnection->prepare("SELECT id, answer FROM Answers WHERE question_code = :code");
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $response->answers = $stmt->fetchAll(PDO::FETCH_CLASS, GetQuestionAnswerModel::class);

        SimpleRouter::response()->json($response)->httpCode(200);
    }

    #[OA\Post(path: '/api/voting/{code}', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(description: 'Vote by code', required: true, content: new OA\JsonContent(ref: '#/components/schemas/VoteByCodeRequestModel'))]
    #[OA\Response(response: 200, description: 'Vote successful')]
    #[OA\Response(response: 400, description: 'Invalid input')]
    #[OA\Response(response: 401, description: 'Already voted or voting hasn\'t been started yet')]
    #[OA\Response(response: 404, description: 'Question not found')]

    public function voteByCode(string $code)
    {
        $model = VoteByCodeRequestModel::constructFromModel(SimpleRouter::request()->getInputHandler()->all());

        if (!$model->isValid()) {
            SimpleRouter::response()->json($model->getErrors())->httpCode(400);
        }

        $query = "SELECT id FROM Voting WHERE question_code = :code AND date_to = null ORDER BY date_from DESC LIMIT 1";
        $stmt = $this->dbConnection->prepare($query);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            throw new APIException("Voting hasn't been started yet", 401);
        }

        $votingId = $stmt->fetchColumn();

        $question = $this->getQuestionByCodeHandler->handle($code);

        if (is_null($question)) {
            throw new APIException('Question not found', 404);
        }

        if ($question->response_type == EQuestionType::TEXT) {
            $field = 'answer_text';
            $param = $model->answerText;
        } else {
            $field = 'selected_answer';
            $param = $model->answerId;
        }

        $insertQuery = "INSERT INTO Vote (voting_id, $field) VALUES (:votingId, :param)";
        $insertStmt = $this->dbConnection->prepare($insertQuery);
        $insertStmt->bindParam(':votingId', $votingId);
        $insertStmt->bindParam(':param', $param);
        $insertStmt->execute();

        SimpleRouter::response()->httpCode(200);
    }

    #[OA\Get(path: '/api/voting/{code}/correct', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get correct answer id', content: new OA\JsonContent(ref: '#/components/schemas/GetCorrectAnswerIdResponseModel'))]
    #[OA\Response(response: 401, description: 'Did not vote yet')]
    #[OA\Response(response: 400, description: 'Question does not have correct answer')]
    #[OA\Response(response: 404, description: 'Question not found')]

    public function getCorrectAnswerId(string $code)
    {
        $stmt = $this->dbConnection->prepare("SELECT id FROM Answers WHERE question_code = :code AND correct = 1");
        $stmt->bindParam(':code', $code);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            throw new APIException('Question does not have correct answer', 400);
        }

        $responseModel = new GetCorrectAnswerIdResponseModel();
        $responseModel->answerIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        SimpleRouter::response()->json($responseModel)->httpCode(200);
    }

    #[OA\Get(path: '/api/voting/{code}/statistics', tags: ['Voting'])]
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
