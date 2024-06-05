<?php

namespace Export;

class CSVExporter implements ExporterInterface
{
    public function export(Test $test)
    {
        $csv_content = '"timestamp","faculty_number","question_number","purpose","type","description","answer 1","answer 2","answer 3","answer 4","correct_answer","difficulty_level","feedback_correct","feedback_incorrect","remarks"' . "\n";

        foreach ($test->getQuestions() as $question) {
            $answers = $question['answers'];
            $csv_content .= "\"{$question['timestamp']}\",\"{$question['faculty_number']}\",\"{$question['question_number']}\",\"{$question['purpose']}\",\"{$question['type']}\",\"{$question['description']}\",";
            $csv_content .= "\"{$answers[0]['answer']}\",\"{$answers[1]['answer']}\",\"{$answers[2]['answer']}\",\"{$answers[3]['answer']}\",";
            $csv_content .= "\"{$question['correct_answer']}\",\"{$question['difficulty_level']}\",\"{$question['feedback_correct']}\",\"{$question['feedback_incorrect']}\",\"{$question['remarks']}\"\n";
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $test->getName() . '.csv"');
        echo $csv_content;
        exit;
    }
}
?>
