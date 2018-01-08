<?php
namespace Cmp\Http\Integration;

interface Monitor
{
    /**
     * @param array $tags
     *
     * @return mixed
     */
    public function start(array $tags = array());

    /**
     * @param array $tags
     *
     * @return mixed
     */
    public function end(array $tags = array());
}