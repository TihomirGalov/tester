<?php

namespace Export;

class CSVExporter implements ExporterInterface
{
    public function export(Test $test)
    {
        $csv_content = "Question,Answer,Is Correct\n";
        foreach ($test->getQuestions() as $question) {
            foreach ($question['answers'] as $answer) {
                $csv_content .= "\"{$question['description']}\",\"{$answer['answer']}\",\"{$answer['is_correct']}\"\n";
            }
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $test->getTestName() . '.csv"');
        echo $csv_content;
        exit;
    }
}
?>