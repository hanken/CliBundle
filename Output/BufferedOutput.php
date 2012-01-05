<?php

namespace FRNK\CliBundle\Output;

use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\OutputInterface;

class BufferedOutput extends StreamOutput {

    private $messages = array();
    private $currentMessage = 0;
    private $stream;
    private $isBuffered;

    public function __construct(StreamOutput $streamOutput, $isBuffered) {
        parent::__construct($streamOutput->getStream(), $streamOutput->getVerbosity(), null, $streamOutput->getFormatter());
        $this->messages[0] = "";
        $this->stream = $streamOutput;
        $this->isBuffered = $isBuffered;
    }

    public function doWrite($message, $newline) {
        if ($newline) {
            if (strlen( $this->messages[$this->currentMessage]) >0){
                $this->currentMessage++;
            }
            $this->messages[$this->currentMessage] = "";
        }
        $this->messages[$this->currentMessage].=$message;
    }

    public function write($messages, $newline = false, $type = 0) {
        if ($this->isBuffered) {

            $messages = explode("\n", $messages);
            foreach ($messages as $message) {
                $this->doWrite($message, true);
            }
        } else {
            $this->stream->write($messages, $newline, $type);
        }
    }

    public function getMessages() {
        return $this->messages;
    }

}

?>
