<?php

namespace Export;

class XMLExporter implements ExporterInterface {
    public function export(Test $test) {
        $filename = $test->getName() . '.xml';
        header('Content-Type: text/xml');
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $xml = new \SimpleXMLElement('<quiz></quiz>');

        foreach ($test->getQuestions() as $question) {
            $questionElement = $xml->addChild('question');
            $questionElement->addAttribute('type', 'multichoice');

            $nameElement = $questionElement->addChild('name');
            $nameElement->addChild('text', htmlspecialchars($question['description']));

            $questionText = $questionElement->addChild('questiontext');
            $questionText->addAttribute('format', 'html');
            $questionText->addChild('text', htmlspecialchars($question['description']));

            $questionElement->addChild('defaultgrade', '1');
            $questionElement->addChild('penalty', '0.3333333');
            $questionElement->addChild('hidden', '0');
            $questionElement->addChild('single', 'true');
            $questionElement->addChild('shuffleanswers', 'true');
            $questionElement->addChild('answernumbering', 'abc');

            foreach ($question['answers'] as $answer) {
                $answerElement = $questionElement->addChild('answer');
                $answerElement->addAttribute('fraction', $answer['is_correct'] ? '100' : '0');
                $answerElement->addChild('text', htmlspecialchars($answer['answer']));
                $answerElement->addChild('feedback');
            }
        }

        echo $xml->asXML();
    }
}
?>
