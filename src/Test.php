<?php

namespace Export;

class Test {
    private $conn;
    private $id;
    private $name;
    private $questions;

    public function __construct($conn, $id) {
        $this->conn = $conn;
        $this->id = $id;
        $this->loadTest();
    }

    private function loadTest() {
        // Fetch test details
        $query = $this->conn->prepare("SELECT name FROM tests WHERE id = ?");
        $query->bind_param("i", $this->id);
        $query->execute();
        $result = $query->get_result();
        $test = $result->fetch_assoc();

        if (!$test) {
            throw new \Exception('Test not found');
        }

        $this->name = $test['name'];

        // Fetch test questions and answers
        $query = $this->conn->prepare("
            SELECT 
                q.id as question_id, 
                q.description, 
                qd.timestamp,
                qd.faculty_number,
                qd.question_number,
                qd.purpose,
                qd.type,
                qd.correct_answer,
                qd.difficulty_level,
                qd.feedback_correct,
                qd.feedback_incorrect,
                qd.remarks,
                a.id as answer_id, 
                a.value, 
                a.is_correct 
            FROM 
                questions q 
            LEFT JOIN 
                question_details qd ON q.id = qd.question_id 
            LEFT JOIN 
                answers a ON q.id = a.question_id 
            WHERE 
                q.test_id = ?
        ");
        $query->bind_param("i", $this->id);
        $query->execute();
        $result = $query->get_result();

        $this->questions = [];
        while ($row = $result->fetch_assoc()) {
            $this->questions[$row['question_id']]['description'] = $row['description'];
            $this->questions[$row['question_id']]['timestamp'] = $row['timestamp'];
            $this->questions[$row['question_id']]['faculty_number'] = $row['faculty_number'];
            $this->questions[$row['question_id']]['question_number'] = $row['question_number'];
            $this->questions[$row['question_id']]['purpose'] = $row['purpose'];
            $this->questions[$row['question_id']]['type'] = $row['type'];
            $this->questions[$row['question_id']]['correct_answer'] = $row['correct_answer'];
            $this->questions[$row['question_id']]['difficulty_level'] = $row['difficulty_level'];
            $this->questions[$row['question_id']]['feedback_correct'] = $row['feedback_correct'];
            $this->questions[$row['question_id']]['feedback_incorrect'] = $row['feedback_incorrect'];
            $this->questions[$row['question_id']]['remarks'] = $row['remarks'];
            $this->questions[$row['question_id']]['answers'][] = [
                'answer_id' => $row['answer_id'],
                'answer' => $row['value'],
                'is_correct' => $row['is_correct']
            ];
        }
    }

    public function getName() {
        return $this->name;
    }

    public function getQuestions() {
        return $this->questions;
    }

    // New method to get test details in a structured format
    public function getTestDetails() {
        return [
            'name' => $this->getName(),
            'questions' => $this->getQuestions()
        ];
    }
}
?>
