# 使用phpunit进行单元测试
### 断言方法
- assertEquals 方法
   > 判断是否为真
- assertStringContainsString
    > 判断返回字符串是否包含
### assert


### 调试快捷键
- F7 通过当前行，进入下一行，如果该行是方法，则进入方法体
- F8 通过当前行，进入下一行，如果该行是方法，也直接进入下一行，不进入方法体
- F9 通过整个流程，全部顺序执行，除非遇到下一个断点
### 要点总结
- 配置 PHP CLI （php.exe 和 xdebug.dll）
- 配置 PHPUnit（autoload.php）
- 配置 phpunit.xml （可选）
- 新增 测试例（测试类 和 测试方法）