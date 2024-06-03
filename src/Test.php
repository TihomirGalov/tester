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
                a.id as answer_id, 
                a.value, 
                a.is_correct 
            FROM 
                questions q 
            LEFT JOIN 
                answers a 
            ON 
                q.id = a.question_id 
            WHERE 
                q.test_id = ?
        ");
        $query->bind_param("i", $this->id);
        $query->execute();
        $result = $query->get_result();

        $this->questions = [];
        while ($row = $result->fetch_assoc()) {
            $this->questions[$row['question_id']]['description'] = $row['description'];
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
}
?>
