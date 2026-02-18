<?php

return new class {
    public function getPrefixesPsr4()
    {
        return [
            'App\\' => [__DIR__ . '/../App'],
            'Other\\' => [__DIR__ . '/../Other'],
            'Vendor\\Package\\' => [__DIR__ . '/../vendor/package'],
        ];
    }
};
