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
use Stuba\Db\Models\Questions\EQuestionType;
use Stuba\Handlers\Voting\CreateVotingByQuestionCodeHandler;
use Stuba\Handlers\Voting\CloseVotingByQuestionCodeHandler;

#[OA\Tag('Voting', description: "Endpointy pre hlasovanie neprihlaseneho pouzivatela")]
class VotingController
{
    private PDO $dbConnection;
    private GetQuestionByCodeHandler $getQuestionByCodeHandler;
    private CreateVotingByQuestionCodeHandler $createVotingByQuestionCodeHandler;
    private CloseVotingByQuestionCodeHandler $closeVotingByQuestionCodeHandler;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
        $this->getQuestionByCodeHandler = new GetQuestionByCodeHandler();
        $this->createVotingByQuestionCodeHandler = new CreateVotingByQuestionCodeHandler();
        $this->closeVotingByQuestionCodeHandler = new CloseVotingByQuestionCodeHandler();

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

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json($response);
    }

    #[OA\Post(path: '/api/voting/{code}', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(description: 'Vote by code', required: true, content: new OA\JsonContent(ref: '#/components/schemas/VoteByCodeRequestModel'))]
    #[OA\Response(response: 200, description: 'Vote successful')]
    #[OA\Response(response: 400, description: 'Invalid input')]
    #[OA\Response(response: 404, description: 'Question not found')]

    public function voteByCode(string $code)
    {
        try {
            $model = new VoteByCodeRequestModel(SimpleRouter::request()->getInputHandler()->all());

            $query = "SELECT id FROM Voting WHERE question_code = :code AND date_to IS NULL ORDER BY date_from DESC LIMIT 1";
            $stmt = $this->dbConnection->prepare($query);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                throw new APIException("Voting hasn't been started yet", 400);
            }

            $votingId = $stmt->fetchColumn();

            $question = $this->getQuestionByCodeHandler->handle($code);

            if (is_null($question)) {
                throw new APIException('Question not found', 404);
            }

            if ($model->getFilledType() != $question->response_type) {
                if ($question->response_type != EQuestionType::MULTIPLE_CHOICE && $model->getFilledType() != EQuestionType::SINGLE_CHOICE) {
                    throw new APIException('Input does not match question type', 400);
                }
            }

            $this->dbConnection->beginTransaction();
            try {
                if ($model->getFilledType() == EQuestionType::TEXT) {
                    $insertQuery = "INSERT INTO Vote (voting_id, answer_text) VALUES (:votingId, :answerText)";
                    $stmt = $this->dbConnection->prepare($insertQuery);
                    $stmt->bindParam(':votingId', $votingId);
                    $stmt->bindParam(':answerText', $model->answerText);
                    $stmt->execute();
                } else if ($model->getFilledType() == EQuestionType::SINGLE_CHOICE) {
                    $insertQuery = "INSERT INTO Vote (voting_id, answer_id) VALUES (:votingId, :answerId)";
                    $stmt = $this->dbConnection->prepare($insertQuery);
                    $stmt->bindParam(':votingId', $votingId);
                    $stmt->bindParam(':answerId', $model->answerIds[0]);
                    $stmt->execute();
                } else if ($model->getFilledType() == EQuestionType::MULTIPLE_CHOICE) {
                    $insertQuery = "INSERT INTO Vote (voting_id, answer_id) VALUES (:votingId, :answerId)";
                    $stmt = $this->dbConnection->prepare($insertQuery);
                    $stmt->bindParam(':votingId', $votingId);
                    foreach ($model->answerIds as $answerId) {
                        $stmt->bindParam(':answerId', $answerId);
                        $stmt->execute();
                    }
                }

                $this->dbConnection->commit();
            } catch (APIException $e) {
                $this->dbConnection->rollBack();
                throw $e;
            }

            SimpleRouter::response()->httpCode(200);
            SimpleRouter::response()->json(['message' => 'Vote successful']);
        } catch (\PDOException $e) {
            SimpleRouter::response()->httpCode(500);
            SimpleRouter::response()->json(['error' => 'Database error: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            SimpleRouter::response()->httpCode(500);
            SimpleRouter::response()->json(['error' => 'Internal Server Error']);
        }
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

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json($responseModel);
    }

    #[OA\Get(path: '/api/voting/{code}/statistics', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question statistics', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionStatisticsResponseModel'))]
    #[OA\Response(response: 404, description: 'Question not found')]
    public function getQuestionStatistics(string $code)
    {
        $responseTypeQuery = "SELECT response_type FROM Questions WHERE question_code = :code";
        $stmt = $this->dbConnection->prepare($responseTypeQuery);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();


        if ($stmt->rowCount() == 0) {
            throw new APIException('Question not found', 404);
        }

        $responseType = $stmt->fetchColumn();

        $votingIdQuery = "SELECT id FROM Voting WHERE question_code = :code AND date_to IS NULL";
        $stmt = $this->dbConnection->prepare($votingIdQuery);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            throw new APIException("Voting hasn't been started yet", 400);
        }

        $votingId = $stmt->fetchColumn();

        if ($responseType === EQuestionType::TEXT->value) {
            $query = "SELECT answer_text AS questionText 
                            FROM Vote 
                            WHERE voting_id = :votingId";
            $stmt = $this->dbConnection->prepare($query);
            $stmt->bindParam(':votingId', $votingId, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() == 0) {
                throw new APIException('No data found for this question', 404);
            }

            SimpleRouter::response()->httpCode(200);
            SimpleRouter::response()->json($results);
        } else {
            $query = "SELECT v.answer_id AS answerId, COUNT(v.answer_id) AS count, (SELECT a.answer FROM Answers a WHERE a.question_code = :code AND a.id = v.answer_id) AS questionText
                      FROM Vote v
                      WHERE v.voting_id = :votingId
                      GROUP BY v.answer_id";
            $stmt = $this->dbConnection->prepare($query);
            $stmt->bindParam(':votingId', $votingId, PDO::PARAM_STR);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                throw new APIException('No data found for this question', 404);
            }

            $results = $stmt->fetchAll(PDO::FETCH_CLASS, GetQuestionStatisticsResponseModel::class);

            SimpleRouter::response()->httpCode(200);
            SimpleRouter::response()->json($results);
        }
    }

    #[OA\Post(path: '/api/voting/{code}/create', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Voting created')]
    #[OA\Response(response: 400, description: 'Voting already exists')]
    #[OA\Response(response: 404, description: 'Question not found')]

    public function createVoting(string $questionCode)
    {
        $this->createVotingByQuestionCodeHandler->handle($questionCode);
        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json(['message' => 'Voting created']);
    }

    #[OA\Post(path: '/api/voting/{code}/close', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(description: 'Close voting request', required: true, content: new OA\JsonContent(properties: [new OA\Property(property: 'note', type: 'string', example: 'Closing note')]))]
    #[OA\Response(response: 200, description: 'Voting closed')]
    #[OA\Response(response: 400, description: 'Voting already closed')]
    #[OA\Response(response: 404, description: 'Question not found')]
    public function closeVoting(string $questionCode)
    {
        $requestData = SimpleRouter::request()->getInputHandler()->all();
        $note = $requestData['note'] ?? '';
        $this->closeVotingByQuestionCodeHandler->handle($questionCode, $note);
        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json(['message' => 'Voting closed']);
    }
}
