<?php
namespace Cmp\Http\Integration;

interface Monitor
{
    /**
     * @param string $metric
     * @param array  $tags
     *
     * @return $this
     */
    public function start($metric, array $tags = array());

    /**
     * @param string $metric
     * @param array  $tags
     *
     * @return $this
     */
    public function end($metric, array $tags = array());
}