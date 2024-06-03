<?php

namespace Export;

class GIFTExporter implements ExporterInterface
{
    public function export(Test $test)
    {
        $questions = $test->getQuestions();
        $giftContent = '';

        foreach ($questions as $question) {
            $giftContent .= $this->formatQuestion($question) . "\n\n";
        }

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment;filename="' . $test->getName() . '.txt"');

        echo $giftContent;
    }

    private function formatQuestion($question)
    {
        $formattedQuestion = '';
        $formattedQuestion .= '::' . $question['description'] . '::';

        if (count($question['answers']) > 1) {
            $formattedQuestion .= "\n{";
            foreach ($question['answers'] as $index => $answer) {
                if ($answer['is_correct']) {
                    $formattedQuestion .= '=';
                } else {
                    $formattedQuestion .= '~';
                }

                $formattedQuestion .= $answer['answer'];

                if ($index < count($question['answers']) - 1) {
                    $formattedQuestion .= ' ';
                }
            }
            $formattedQuestion .= '}';
        }

        return $formattedQuestion;
    }
}

?>
