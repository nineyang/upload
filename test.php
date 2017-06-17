<?php
$handler = @fopen('./tmp/3.png' , 'r');
echo ftell($handler) . PHP_EOL;

// 设置到开头位置
fseek($handler , 0);

echo ftell($handler) . PHP_EOL;

// 设置到末尾位置
fseek($handler , 0 , SEEK_END);

echo ftell($handler) . PHP_EOL;

// 从当前指针往后移100位
fseek($handler , 100 , SEEK_CUR);

echo ftell($handler) . PHP_EOL;

// 设置到离开头100的位置
fseek($handler , 100 , SEEK_SET);

echo ftell($handler) . PHP_EOL;