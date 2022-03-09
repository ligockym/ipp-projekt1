<?php

class Variable implements Symbol
{
    private string $name;
    private string $frame;

    /**
     * @param string $name
     * @param string $frame
     */
    public function __construct(string $name, string $frame)
    {
        $this->name = $name;
        $this->frame = $frame;
    }


    public function can_be_used_as(DATA_TYPE $data_type): bool
    {
        // Can be used anywhere except as type and label.
        return ($data_type !== DATA_TYPE::TYPE) && ($data_type !== DATA_TYPE::LABEL);
    }

    public function to_xml(): string
    {
        $name_escaped = htmlspecialchars($this->name);
        return "$this->frame@$name_escaped";
    }

    public function get_type(): DATA_TYPE
    {
        return DATA_TYPE::VAR;
    }
}