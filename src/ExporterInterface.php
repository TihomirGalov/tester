<?php

namespace Export;

interface ExporterInterface {
    public function export(Test $test);
}
?>