<?php

namespace Honeybee\Core\Queue\Job;

use Honeybee\Core\Queue;

class IpcMessaging
{
    const READ_SIZE = 1000;

    protected $msg_queue;

    protected $msg_type;

    public function __construct($queue_path, $identifier, $msg_type)
    {
        touch($queue_path);
        $queue_key = ftok($queue_path, $identifier);
        $this->msg_queue = msg_get_queue($queue_key);
        $this->msg_type = $msg_type;
    }

    public function send($message, $msg_type = null)
    {
        $msg_type = $msg_type ? $msg_type : $this->msg_type;
        $error_code = null;

        if (msg_send($this->msg_queue, $msg_type, $message, false, true, $error_code))
        {
            return true;
        }
        else
        {
            // @todo handle error, need to find out what the msg_send error codes are.
            return false;
        }
    }

    public function read($msg_type = null)
    {
        $message = null;
        $msg_type = $msg_type ? $msg_type : $this->msg_type;
        $actual_msg_type = null;
        $error_code = null;

        if (
            msg_receive(
                $this->msg_queue,
                $msg_type,
                $actual_msg_type,
                self::READ_SIZE,
                $message,
                false,
                MSG_IPC_NOWAIT,
                $error_code
            )
        ) {
            return $message;
        } else {
            // @todo handle error, need to find out what the msg_receive error codes are.
            return false;
        }
    }

    public function getStats()
    {
        return msg_stat_queue($this->msg_queue);
    }

    public function destroy()
    {
        msg_remove_queue($this->msg_queue);
    }
}
