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

$program = new Program();
while ($line = fgets(STDIN)) {
    $program->parse_line($line);
}

echo $program->to_xml();