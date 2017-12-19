<?php
namespace Cmp\Http\Integration;

class NullMonitor implements Monitor
{
    /**
     * @param string $metric
     * @param array  $tags
     *
     * @return $this
     */
    public function start($metric, array $tags = array())
    {
        return $this;
    }

    /**
     * @param string $metric
     * @param array  $tags
     *
     * @return $this
     */
    public function end($metric, array $tags = array())
    {
        return $this;
    }
}
