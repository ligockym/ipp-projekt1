<?php

interface Symbol
{
    public function can_be_used_as(DATA_TYPE $data_type): bool;
    public function to_xml(): string;
    public function get_type(): DATA_TYPE;
}