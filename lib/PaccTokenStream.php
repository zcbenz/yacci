<?php
/**
 * Token stream
 */
interface PaccTokenStream
{
    /**
     * @return string
     */
    function remainder();

    /**
     * @return PaccToken
     */
    function current();

    /**
     * @return PaccToken
     */
    function next();
}
