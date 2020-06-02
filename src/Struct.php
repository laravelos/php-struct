<?php

declare(strict_types = 1);

namespace Laravelos;

use Laravelos\Traits\StructGetter;
use Laravelos\Traits\StructSetter;
use Laravelos\Traits\ToArray;

abstract class Struct
{
    use StructSetter;
    use StructGetter;
    use ToArray;

    public function __construct(array $data = [])
    {
        if (extension_loaded('Zend OPcache') && ini_get('opcache.save_comments') == 0) {
            $this->optimizerPlusLoadComments();
        }

        if ($data) {
            $this->createClassByStruct($this, $data);
        }
    }
}
