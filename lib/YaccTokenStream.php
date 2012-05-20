<?php
/**
 * Token stream
 */
interface YaccTokenStream
{
    /**
     * @return string
     */
    function remainder();

    /**
     * @return YaccToken
     */
    function current();

    /**
     * @return YaccToken
     */
    function next();
}
