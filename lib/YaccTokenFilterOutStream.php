<?php
/**
 * Filteres some tokens from stream
 */
class YaccTokenFilterOutStream implements YaccTokenStream
{
    /**
     * Stream
     * @var YaccTokenStream
     */
    private $stream;

    /**
     * Filters out there tokens
     * @var array
     */
    private $out;

    /**
     * Initializes filter stream
     * @param YaccTokenStreamable stream to be filtered
     * @param array tokens we do not want
     */
    public function __construct(YaccTokenStream $stream, $out = NULL)
    {
        $this->stream = $stream;
        if (!is_array($out)) { $out = func_get_args(); array_shift($out); }
        $this->out = array_flip($out);
    }

    /**
     * Get remainder of stream
     * @return string
     */
    public function remainder()
    {
        return $this->stream->remainder();
    }

    /**
     * Get current token
     * @retrun YaccToken
     */
    public function current()
    {
        return $this->stream->current();
    }

    /**
     * Get next token
     * @return YaccToken
     */
    public function next()
    {
        do {
            $token = $this->stream->next();
        } while (!($token instanceof YaccEndToken) && isset($this->out[get_class($token)]));
        return $token;
    }
}
