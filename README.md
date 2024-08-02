# readlines

读取文件行数及输出代码，一般用于申请软著
# 安装

~~~
composer require thefunpower/readlines
~~~

# 使用
~~~ 
$readlines = new \CReadLines\Reader();
//基础目录
$readlines->line_base_dir = $base = __DIR__.'/../';
//计算行数的目录
$readlines->line_code_path = [
    $base.'core/sys',
    $base.'core/employee', 
    $base.'base',
];
$readlines->output = $base.'/lines.txt'; 
$readlines->allow_ext = ['php']; //默认是PHP，可不用添加此行
$readlines->run();
~~~
