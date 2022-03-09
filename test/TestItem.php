<?php

class TestItem
{
    private string $path;
    private string $src_path;
    private string $parser_output_path;
    private string $ret_code_path;

    private bool $is_correct;
    private bool $is_correct_ret_code;
    private string $xml_delta_path;
    private int $ret_code;
    private int $reference_ret_code;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;

        $this->src_path = $this->path . '.src';
        $this->parser_output_path = $this->path . '.xml';
        $this->ret_code_path = $this->path . '.parser_code';
        $this->xml_delta_path = $this->path . '.delta.xml';
        $this->is_correct_ret_code = false;
        $this->ret_code = 0;
        $this->reference_ret_code = 0;
        $this->is_correct = false;

    }

    public function test(?string $parser_path = null, ?string $interpreter_path = null): bool
    {
        if ($parser_path) {
            $this->run_parser($parser_path);

            $is_code_same = $this->test_return_code();

            // test content only if return code is 0
            if ($is_code_same && $this->ret_code === 0) {
                $is_output_same = $this->test_output();
            } else {
                $is_output_same = true; // when not 0, content is not important
            }

            $this->is_correct = $is_code_same && $is_output_same;
        }
        return $this->is_correct;
    }

    private function run_parser($parser_path): void
    {

        exec("php8.1 $parser_path < $this->src_path > $this->parser_output_path", $output, $err_code);
        file_put_contents($this->ret_code_path, $err_code);
    }

    public function clean_after_parser(): void
    {
     //   exec("rm $this->parser_output_path && rm $this->parser_ret_code_path && rm $this->xml_delta_path");
    }

    private function test_return_code(): bool
    {
        $reference_path = $this->path . '.rc';

        // load reference return code
        $this->reference_ret_code = file_get_contents($reference_path);
        $this->ret_code = file_get_contents($this->ret_code_path);

        $this->is_correct_ret_code = $this->reference_ret_code === $this->ret_code;
        return $this->is_correct_ret_code;
    }

    private function test_output(): bool
    {
        $reference_result = $this->path . '.out';

        exec("java -jar jexamxml/jexamxml.jar $this->parser_output_path $reference_result $this->xml_delta_path jexamxml/options", $output, $err_code);

        if ($err_code === 0) {
            return true;
        } else {
            return false;
        }
    }

    public function generate_missing_files()
    {
        $reference_output = $this->path . '.out';
        $reference_code = $this->path . '.rc';
        $in = $this->path . '.in';

        if (!is_file($reference_output)) {
            file_put_contents($reference_output, '');
        }
        if (!is_file($in)) {
            file_put_contents($in, '');
        }
        if (!is_file($reference_code)) {
            file_put_contents($in, '0');
        }
    }

    public function get_path()
    {
        return $this->path;
    }

    public function is_correct()
    {
        return $this->is_correct;
    }

    public function get_xml_delta()
    {
        if (is_file($this->xml_delta_path)) {
            file_get_contents($this->xml_delta_path);
        }
        return "";
    }

    public function get_ret_code()
    {
        return $this->ret_code;
    }

    public function is_correct_ret_code(): bool
    {
        return $this->is_correct_ret_code;
    }

    public function get_reference_ret_code() {
        return $this->reference_ret_code;
    }
}