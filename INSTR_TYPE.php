<?php
enum INSTR_TYPE: string
{
    case MOVE = "move";
    case CREATEFRAME = "createframe";
    case PUSHFRAME = "pushframe";
    case POPFRAME = "popframe";
    case DEFVAR = "defvar";
    case CALL = "call";
    case RETURN = "return";
    case PUSHS = "pushs";
    case POPS = "pops";
    case ADD = "add";
    case SUB = "sub";
    case MUL = "mul";
    case IDIV = "idiv";
    case LT = "lt";
    case GT = "gt";
    case EQ = "eq";
    case AND = "and";
    case OR = "or";
    case NOT = "not";
    case INT2CHAR = "int2char";
    case STRI2INT = "stri2int";
    case READ = "read";
    case WRITE = "write";
    case CONCAT = "concat";
    case STRLEN = "strlen";
    case GETCHAR = "getchar";
    case SETCHAR = "setchar";
    case TYPE = "type";
    case LABEL = "label";
    case JUMP = "jump";
    case JUMPIFEQ = "jumpifeq";
    case JUMPIFNEQ = "jumpifneq";
    case EXIT = "exit";
    case DPRINT = "dprint";
    case BREAK = "break";
}