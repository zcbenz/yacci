<?php
/**
 * Converts string into stream of tokens
 */
class YaccLexer implements YaccTokenStream
{
    /**
     * Mapping from token regexes to token classes
     * @var array
     */
    private static $map = array(
        '/^(%%)/S'                                                   => 'YaccSectionToken',
        '/^%union {(.*?)}/Ss'                                        => 'YaccUnionToken',
        '/^%{(.*?)%}/Ss'                                             => 'YaccPrologueToken',
        '/^(%[a-zA-Z][a-zA-Z_]*)/S'                                  => 'YaccDeclarationToken',
        '/^(\s+)/Ss'                                                 => 'YaccWhitespaceToken',
        '/^([a-zA-Z][a-zA-Z_]*)/S'                                   => 'YaccIdToken',
        '/^(\'(?:\\\'|[^\'])*\'|"(?:\\"|[^"])*"|`(?:\\`|[^`])*`)/SU' => 'YaccStringToken',
        '/^(@|\\\\|\\.|=|\(|\)|:|\||\{|\}|;)/S'                      => 'YaccSpecialToken',
        '/^(\/\*.*\*\/)/SUs'                                         => 'YaccCommentToken',
        '/^<([a-zA-Z][a-zA-Z_]*)>/Ss'                                => 'YaccTypeToken',
        '/^(.)/Ss'                                                   => 'YaccBadToken',
    );

    /**
     * String to tokenize
     * @var string
     */
    private $string = '';

    /**
     * Current token
     * @var YaccToken
     */
    private $current = NULL;

    /**
     * Current line of string to tokenize
     * @var
     */
    private $line = 1;

    /**
     * Current position on current line of string to tokenize
     * @var int
     */
    private $position = 1;

    /**
     * Buffered tokens
     * @var array
     */
    private $buffer = array();

    /**
     * Initializes lexer
     * @param string string to tokenize
     * @param int
     */
    public function __construct($string = '', $start_line = 1)
    {
        $this->line = $start_line;
        $this->string = $string;
    }

    /**
     * Get remainder of string
     * @return string
     */
    public function remainder()
    {
        $remainder = $this->string;
        $this->string = '';
        return $remainder;
    }

    /**
     * Get current token
     * @return YaccToken
     */
    public function current()
    {
        if ($this->current === NULL) { $this->lex(); }
        return $this->current;
    }

    /**
     * Synonynm for lex()
     * @return YaccToken
     */
    public function next()
    {
        return $this->lex();
    }

    /**
     * Get next token
     * @return YaccToken
     */
    public function lex()
    {
        if (!empty($this->buffer)) { return $this->current = array_shift($this->buffer); }
        if (empty($this->string)) { return $this->current = new YaccEndToken(NULL, $this->line, $this->position); }

        foreach (self::$map as $regex => $class) {
            if (!preg_match($regex, $this->string, $m)) { continue; }

            $token = new $class($m[1], $this->line, $this->position);

            if ($token instanceof YaccSpecialToken && $m[1] === '{') {
                $offset = 0;
                do {
                    if (($rbrace = strpos($this->string, '}', $offset)) === FALSE) {
                        array_push($this->buffer, new YaccCodeToken($this->string, $this->line, $this->position + 1));
                        return $this->current = $token;
                    }

                    $offset = $rbrace + 1;
                    $code = substr($this->string, 0, $rbrace + 1);
                    $test = preg_replace($r = '#"((?<!\\\\)\\\\"|[^"])*$
                                          |"((?<!\\\\)\\\\"|[^"])*"
                                          |\'((?<!\\\\)\\\\\'|[^\'])*\'
                                          |\'((?<!\\\\)\\\\\'|[^\'])*$
                                          #x', '', $code);

                } while (substr_count($test, '{') !== substr_count($test, '}'));

                $code = substr($code, 1, strlen($code) - 2);
                array_push($this->buffer, new YaccCodeToken($code, $this->line, $this->position + 1));
                $m[0] .= $code;
            }

            break;
        }

        $lines = substr_count($m[0], "\n");
        $this->line += $lines;

        if ($lines > 0) { $this->position = strlen(end(preg_split("/\r?\n|\r/", $m[0]))) + 1; }
        else { $this->position += strlen($m[0]); }

        $this->string = substr($this->string, strlen($m[0]));

        return $this->current = $token;
    }

    /**
     * Creates instance from string
     * @param string
     * @param int
     * @return self
     */
    public static function fromString($string, $start_line = 1)
    {
        return new self($string, $start_line);
    }

    /**
     * Creates instance from file
     * @param string
     * @return self
     */
    public static function fromFile($filename)
    {
        return self::fromString(file_get_contents($filename));
    }
}
