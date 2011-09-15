<?php

class RssDataRecord extends ImportBaseDataRecord
{
    // ---------------------------------- <CONSTANTS> --------------------------------------------
    const PROP_AUTHOR = 'author';
    const PROP_TIMESTAMP = 'timestamp';
    // ---------------------------------- <CONSTANTS> --------------------------------------------

    var $data;

    // ---------------------------------- <ImportBaseDataRecord IMPL> ----------------------------

    protected function setAuthor($param)
    {
        $this->data['author'] = $param;
    }

    public function getAuthor()
    {
        return $this->data['author'];
    }

    protected function setTimestamp($param)
    {
        $this->data['lastchanged'] = new DateTime($param);
        $this->data['timestamp'] = $this->data['lastchanged']->format('c');
    }

    public function getTimestamp()
    {
        return $this->data['lastchanged']->format(DATE_ISO8601);
    }

    public function getExposedProperties()
     {
        return array_merge(
            parent::getExposedProperties(),
            array(self::PROP_AUTHOR, self::PROP_TIMESTAMP));
    }



    /**
     * Parse the incoming feed item data
     *
     * @param       mixed $data
     *
     * @return      array
     *
     * @see         ImportBaseDataRecord::parse()
     */
    protected function parseData($data)
    {
        $this->data = $data;

        return array(
            self::PROP_IDENT => $data['url'],
            self::PROP_TITLE => $data['title'],
            self::PROP_CONTENT => empty($data['html']) ? htmlspecialchars($data['teaser_text']) : $data['html'],
        );
    }


    // ---------------------------------- </ImportBaseDataRecord IMPL> ---------------------------
}

?>