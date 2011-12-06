<?php
    if (isset($t['_content']))
    {
        ob_clean();
        echo $t['_content'];
    }
