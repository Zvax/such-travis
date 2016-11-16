<?php
declare(strict_types = 1);
namespace Berilium\Controller;
class Front
{
    public function home(): string
    {
        echo 'from controller home';
        return '';
    }
}
