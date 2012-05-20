<?php
/**
 * Identifier token
 */
class YaccIdToken extends YaccToken {}

/**
 * String token
 */
class YaccStringToken extends YaccToken
{
    protected function value()
    {
        // FIXME: eval is evil!
        if ($this->lexeme[0] === '"' || $this->lexeme[0] === "'") {
            $this->value = eval('return ' . $this->lexeme . ';');
        } else {
            $this->value = substr($this->lexeme, 1, strlen($this->lexeme) - 2);
        }
    }
}

/**
 * Special character token
 */
class YaccSpecialToken extends YaccToken {}

/**
 * Code token
 */
class YaccCodeToken extends YaccToken {}

/**
 * Whitespace token
 */
class YaccWhitespaceToken extends YaccToken {}

/**
 * Comment token
 */
class YaccCommentToken extends YaccToken {}

/**
 * Section token
 */
class YaccSectionToken extends YaccToken {}

/**
 * C Header token
 */
class YaccPrologueToken extends YaccToken {}

/**
 * Union token
 */
class YaccUnionToken extends YaccToken {}

/**
 * Type token
 */
class YaccTypeToken extends YaccToken {}

/**
 * Yacc declaration token
 */
class YaccDeclarationToken extends YaccToken {}

/**
 * End token
 */
class YaccEndToken extends YaccToken {}

/**
 * Bad token
 */
class YaccBadToken extends YaccToken {}
