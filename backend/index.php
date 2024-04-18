<?php
require_once __DIR__ . '/../vendor/autoload.php';
$queryArray;
error_reporting(E_ALL);
ini_set('display_errors', '1');
parse_str($_SERVER['QUERY_STRING'], $queryArray);

try {
    if ($queryArray["class"] == "prizes") {
        require "prizes/controller.php";
        $controller = new PrizesController();
        require_once "jwt/TokenHandler.php";
        $tokenHandler = new TokenHandler;
        $isAuthorized = $tokenHandler->isAuthorized();
        $response = $controller->handle($_SERVER['REQUEST_METHOD'], $queryArray, json_decode(file_get_contents('php://input'), true), $isAuthorized);
        echo json_encode($response);
    } else if ($queryArray['class'] == 'ais') {
        require "ais/TimetableController.php";
        $controller = new AISController();
        $response = $controller->loadTimetable();
        echo $response;
    } else if ($queryArray["class"] == "timetable") {
        require "timetable/Controller.php";
        $controller = new TimetableController();
        $response = $controller->handle($_SERVER['REQUEST_METHOD'], $queryArray, json_decode(file_get_contents('php://input'), true));
        echo json_encode($response);
    } else if ($queryArray["class"] == "thesis") {
        require "ais/ThesisController.php";
        $controller = new ThesisController();
        $response = $controller->handle($_SERVER['REQUEST_METHOD'], $queryArray, $_GET);
        echo json_encode($response);
    } else if ($queryArray['class'] == 'study-programme') {
        require "ais/StudyProgrammeController.php";
        $controller = new StudyProgrammeController();
        $response = $controller->handle($_SERVER['REQUEST_METHOD'], $queryArray, $_GET);
        echo json_encode($response);
    } else if ($queryArray['class'] == 'supervisor') {
        require "ais/SupervisorController.php";
        $controller = new SupervisorController();
        $response = $controller->handle($_SERVER['REQUEST_METHOD'], $queryArray, $_GET);
        echo json_encode($response);
    }
} catch (APIException $e) {
    http_response_code($e->getCode());
    echo json_encode(array("message" => $e->getMessage()));
}

