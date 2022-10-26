<?php
ini_set('display_errors', 'stderr');

spl_autoload_register(function ($class_name) {
    if (!class_exists($class_name)) {
        include_once $class_name . '.php';
    }
});

// Help argument
if (isset($argv[1]) && $argv[1] == '--help') {
    echo "Script parse.php loads source code in language IPPcode22 from the standard input. It checks lexical and syntactical correctness of the code and prints XML representation of the program to command line.\n
Error codes: 
• 21 - wrong header in source code written in IPPcode22
• 22 - unknown or wrong operation code in source code written in IPPcode22
• 23 - other lexical or syntactic error in source code written in IPPcode22\n";
    exit(0);
}

// Including DATA_TYPE::VAR, because it can be userd anywhere except <label> and <type>
$datable_types = [DATA_TYPE::BOOL, DATA_TYPE::INT, DATA_TYPE::STRING];
$datable_types_nil = [DATA_TYPE::BOOL, DATA_TYPE::INT, DATA_TYPE::NIL, DATA_TYPE::STRING];

$second_third_same = function(Instruction $instruction) {
    $args = $instruction->get_args();
    $arg1 = $args[1]->get_type();
    $arg2 = $args[2]->get_type();
    return $arg1 == $arg2 || ($arg1 == DATA_TYPE::VAR) || ($arg2 == DATA_TYPE::VAR);
};

$instruction_formats = [
    new InstructionFormat(INSTR_TYPE::MOVE, [DATA_TYPE::VAR], $datable_types_nil),
    new InstructionFormat(INSTR_TYPE::CREATEFRAME),
    new InstructionFormat(INSTR_TYPE::PUSHFRAME),
    new InstructionFormat(INSTR_TYPE::POPFRAME),
    new InstructionFormat(INSTR_TYPE::DEFVAR, [DATA_TYPE::VAR]),
    new InstructionFormat(INSTR_TYPE::CALL, [DATA_TYPE::LABEL]),
    new InstructionFormat(INSTR_TYPE::RETURN),
    new InstructionFormat(INSTR_TYPE::PUSHS, $datable_types_nil),
    new InstructionFormat(INSTR_TYPE::POPS, [DATA_TYPE::VAR]),
    new InstructionFormat(INSTR_TYPE::ADD, [DATA_TYPE::VAR], [DATA_TYPE::INT], [DATA_TYPE::INT]),
    new InstructionFormat(INSTR_TYPE::SUB, [DATA_TYPE::VAR], [DATA_TYPE::INT], [DATA_TYPE::INT]),
    new InstructionFormat(INSTR_TYPE::MUL, [DATA_TYPE::VAR], [DATA_TYPE::INT], [DATA_TYPE::INT]),
    new InstructionFormat(INSTR_TYPE::IDIV, [DATA_TYPE::VAR], [DATA_TYPE::INT], [DATA_TYPE::INT]),
    new InstructionFormat(INSTR_TYPE::LT, [DATA_TYPE::VAR], $datable_types, $datable_types, $second_third_same),
    new InstructionFormat(INSTR_TYPE::GT, [DATA_TYPE::VAR], $datable_types, $datable_types, $second_third_same),
    new InstructionFormat(INSTR_TYPE::EQ, [DATA_TYPE::VAR], $datable_types_nil, $datable_types_nil, $second_third_same),
    new InstructionFormat(INSTR_TYPE::AND, [DATA_TYPE::VAR], [DATA_TYPE::BOOL], [DATA_TYPE::BOOL]),
    new InstructionFormat(INSTR_TYPE::OR, [DATA_TYPE::VAR], [DATA_TYPE::BOOL], [DATA_TYPE::BOOL]),
    new InstructionFormat(INSTR_TYPE::NOT, [DATA_TYPE::VAR], [DATA_TYPE::BOOL]),
    new InstructionFormat(INSTR_TYPE::INT2CHAR, [DATA_TYPE::VAR], [DATA_TYPE::INT]),
    new InstructionFormat(INSTR_TYPE::STRI2INT, [DATA_TYPE::VAR], [DATA_TYPE::STRING], [DATA_TYPE::INT]),
    new InstructionFormat(INSTR_TYPE::READ, [DATA_TYPE::VAR], [DATA_TYPE::TYPE]),
    new InstructionFormat(INSTR_TYPE::WRITE, $datable_types_nil),
    new InstructionFormat(INSTR_TYPE::CONCAT, [DATA_TYPE::VAR], [DATA_TYPE::STRING], [DATA_TYPE::STRING]),
    new InstructionFormat(INSTR_TYPE::STRLEN, [DATA_TYPE::VAR], [DATA_TYPE::STRING]),
    new InstructionFormat(INSTR_TYPE::GETCHAR, [DATA_TYPE::VAR], [DATA_TYPE::STRING], [DATA_TYPE::INT]),
    new InstructionFormat(INSTR_TYPE::SETCHAR, [DATA_TYPE::VAR], [DATA_TYPE::INT], [DATA_TYPE::STRING]),
    new InstructionFormat(INSTR_TYPE::TYPE, [DATA_TYPE::VAR], $datable_types_nil),
    new InstructionFormat(INSTR_TYPE::LABEL, [DATA_TYPE::LABEL]),
    new InstructionFormat(INSTR_TYPE::JUMP, [DATA_TYPE::LABEL]),
    new InstructionFormat(INSTR_TYPE::JUMPIFEQ, [DATA_TYPE::LABEL], $datable_types_nil, $datable_types_nil),
    new InstructionFormat(INSTR_TYPE::JUMPIFNEQ, [DATA_TYPE::LABEL], $datable_types_nil, $datable_types_nil),
    new InstructionFormat(INSTR_TYPE::EXIT, [DATA_TYPE::INT]),
    new InstructionFormat(INSTR_TYPE::DPRINT, $datable_types_nil),
    new InstructionFormat(INSTR_TYPE::BREAK),
];

$program = new Program($instruction_formats);
while ($line = fgets(STDIN)) {
    // load line
    $program->parse_line($line);
}

echo $program->to_xml();