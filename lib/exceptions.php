<?php
/**
 * Thrown if there is some unexpected token in stream
 */
class YaccUnexpectedToken extends Exception
{
    /**
     * @var YaccToken
     */
    public $token;

    public function __construct(YaccToken $t, Exception $previous = NULL)
    {
        $this->token = $t;
        parent::__construct(
            'Unexcepted token `' . $t->lexeme .
            '` of type ' . get_class($t) . 
            ' on line ' . $t->line . 
            ' at position ' . $t->position .
            '.',
            0,
            $previous
        );
    }
}

/**
 * Thrown when token stream unexpectedly ended
 */
class YaccUnexpectedEnd extends Exception
{
    public function __construct(Exception $previous = NULL)
    {
        parent::__construct(
            'Unexcepted end.',
            0,
            $previous
        );
    }
}

/**
 * Thrown if there is something bad with some identifier (e.g. bad caps)
 */
class YaccBadIdentifier extends Exception
{
    /**
     * @var YaccToken
     */
    public $token;

    public function __construct(YaccToken $t, Exception $previous = NULL)
    {
        $this->token = $t;
        parent::__construct(
            'Bad identifier `' . $t->value . 
            '` on line ' . $t->line . 
            ' at position ' . $t->position .
            '.',
            0,
            $previous
        );
    }
}
