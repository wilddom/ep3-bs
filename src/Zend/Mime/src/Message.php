<?php
/**
 * @see       https://github.com/zendframework/zend-mime for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mime/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mime;

class Message
{
    protected $parts = [];
    protected $mime = null;

    /**
     * Returns the list of all Zend\Mime\Part in the message
     *
     * @return Part[]
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * Sets the given array of Zend\Mime\Part as the array for the message
     *
     * @param array $parts
     * @return self
     */
    public function setParts($parts)
    {
        $this->parts = $parts;
        return $this;
    }

    /**
     * Append a new Zend\Mime\Part to the current message
     *
     * @param \Zend\Mime\Part $part
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function addPart(Part $part)
    {
        foreach ($this->getParts() as $row) {
            if ($part == $row) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Provided part %s already defined.',
                    $part->getId()
                ));
            }
        }

        $this->parts[] = $part;
        return $this;
    }

    /**
     * Check if message needs to be sent as multipart
     * MIME message or if it has only one part.
     *
     * @return bool
     */
    public function isMultiPart()
    {
        return (count($this->parts) > 1);
    }

    /**
     * Set Zend\Mime\Mime object for the message
     *
     * This can be used to set the boundary specifically or to use a subclass of
     * Zend\Mime for generating the boundary.
     *
     * @param \Zend\Mime\Mime $mime
     * @return self
     */
    public function setMime(Mime $mime)
    {
        $this->mime = $mime;
        return $this;
    }

    /**
     * Returns the Zend\Mime\Mime object in use by the message
     *
     * If the object was not present, it is created and returned. Can be used to
     * determine the boundary used in this message.
     *
     * @return \Zend\Mime\Mime
     */
    public function getMime()
    {
        if ($this->mime === null) {
            $this->mime = new Mime();
        }

        return $this->mime;
    }

    /**
     * Generate MIME-compliant message from the current configuration
     *
     * This can be a multipart message if more than one MIME part was added. If
     * only one part is present, the content of this part is returned. If no
     * part had been added, an empty string is returned.
     *
     * Parts are separated by the mime boundary as defined in Zend\Mime\Mime. If
     * {@link setMime()} has been called before this method, the Zend\Mime\Mime
     * object set by this call will be used. Otherwise, a new Zend\Mime\Mime object
     * is generated and used.
     *
     * @param string $EOL EOL string; defaults to {@link Zend\Mime\Mime::LINEEND}
     * @return string
     */
    public function generateMessage($EOL = Mime::LINEEND)
    {
        if (! $this->isMultiPart()) {
            if (empty($this->parts)) {
                return '';
            }
            $part = current($this->parts);
            $body = $part->getContent($EOL);
        } else {
            $mime = $this->getMime();

            $boundaryLine = $mime->boundaryLine($EOL);
            $body = 'This is a message in Mime Format.  If you see this, '
                  . "your mail reader does not support this format." . $EOL;

            foreach (array_keys($this->parts) as $p) {
                $body .= $boundaryLine
                       . $this->getPartHeaders($p, $EOL)
                       . $EOL
                       . $this->getPartContent($p, $EOL);
            }

            $body .= $mime->mimeEnd($EOL);
        }

        return trim($body);
    }

    /**
     * Get the headers of a given part as an array
     *
     * @param int $partnum
     * @return array
     */
    public function getPartHeadersArray($partnum)
    {
        return $this->parts[$partnum]->getHeadersArray();
    }

    /**
     * Get the headers of a given part as a string
     *
     * @param int $partnum
     * @param string $EOL
     * @return string
     */
    public function getPartHeaders($partnum, $EOL = Mime::LINEEND)
    {
        return $this->parts[$partnum]->getHeaders($EOL);
    }

    /**
     * Get the (encoded) content of a given part as a string
     *
     * @param int $partnum
     * @param string $EOL
     * @return string
     */
    public function getPartContent($partnum, $EOL = Mime::LINEEND)
    {
        return $this->parts[$partnum]->getContent($EOL);
    }

    /**
     * Explode MIME multipart string into separate parts
     *
     * Parts consist of the header and the body of each MIME part.
     *
     * @param string $body
     * @param string $boundary
     * @throws Exception\RuntimeException
     * @return array
     */
    // @codingStandardsIgnoreStart
    protected static function _disassembleMime($body, $boundary)
    {
        // @codingStandardsIgnoreEnd
        $start  = 0;
        $res    = [];
        // find every mime part limiter and cut out the
        // string before it.
        // the part before the first boundary string is discarded:
        $p = strpos($body, '--' . $boundary."\n", $start);
        if ($p === false) {
            // no parts found!
            return [];
        }

        // position after first boundary line
        $start = $p + 3 + strlen($boundary);

        while (($p = strpos($body, '--' . $boundary . "\n", $start)) !== false) {
            $res[] = substr($body, $start, $p - $start);
            $start = $p + 3 + strlen($boundary);
        }

        // no more parts, find end boundary
        $p = strpos($body, '--' . $boundary . '--', $start);
        if ($p === false) {
            throw new Exception\RuntimeException('Not a valid Mime Message: End Missing');
        }

        // the remaining part also needs to be parsed:
        $res[] = substr($body, $start, $p - $start);
        return $res;
    }

    /**
     * Decodes a MIME encoded string and returns a Zend\Mime\Message object with
     * all the MIME parts set according to the given string
     *
     * @param string $message
     * @param string $boundary Multipart boundary; if omitted, $message will be
     *     treated as a single part.
     * @param string $EOL EOL string; defaults to {@link Zend\Mime\Mime::LINEEND}
     * @throws Exception\RuntimeException
     * @return Message
     */
    public static function createFromMessage($message, $boundary = null, $EOL = Mime::LINEEND)
    {
        if ($boundary) {
            $parts = Decode::splitMessageStruct($message, $boundary, $EOL);
        } else {
            Decode::splitMessage($message, $headers, $body, $EOL);
            $parts = [[
                'header' => $headers,
                'body'   => $body,
            ]];
        }

        $res = new static();
        foreach ($parts as $part) {
            // now we build a new MimePart for the current Message Part:
            $properties = [];
            foreach ($part['header'] as $header) {
                /** @var \Zend\Mail\Header\HeaderInterface $header */
                /**
                 * @todo check for characterset and filename
                 */

                $fieldName  = $header->getFieldName();
                $fieldValue = $header->getFieldValue();
                switch (strtolower($fieldName)) {
                    case 'content-type':
                        $properties['type'] = $fieldValue;
                        break;
                    case 'content-transfer-encoding':
                        $properties['encoding'] = $fieldValue;
                        break;
                    case 'content-id':
                        $properties['id'] = trim($fieldValue, '<>');
                        break;
                    case 'content-disposition':
                        $properties['disposition'] = $fieldValue;
                        break;
                    case 'content-description':
                        $properties['description'] = $fieldValue;
                        break;
                    case 'content-location':
                        $properties['location'] = $fieldValue;
                        break;
                    case 'content-language':
                        $properties['language'] = $fieldValue;
                        break;
                    default:
                        // Ignore unknown header
                        break;
                }
            }

            $body = $part['body'];

            if (isset($properties['encoding'])) {
                switch ($properties['encoding']) {
                    case 'quoted-printable':
                        $body = quoted_printable_decode($body);
                        break;
                    case 'base64':
                        $body = base64_decode($body);
                        break;
                }
            }

            $newPart = new Part($body);
            foreach ($properties as $key => $value) {
                $newPart->$key = $value;
            }
            $res->addPart($newPart);
        }

        return $res;
    }
}
