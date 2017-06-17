## 前言
文件的上传是我们做项目中一个必不可少的环节，但是偶尔我们也会遇到一些比较吃内存的大文本，对于这种文本，我们可以采用先切割，才分块上传，最后再在服务端合并的过程。如下图所示:

![未命名文件 (1).png][1]

具体的代码我已经放置到GitHub上，有需自取。

## 实现过程
1. 分割文本
```
// 设置切割文件的大小(单位kb)
    const LIMIT = 40000;
// 设置后台的url
    var url = 'upload.php';
// 记录开始大小
    var start = 0;
// 记录游标
    var offset = 0;

    $(function(){
        var startUpload = function(file){
            while(file.size > start){
                var form = new FormData();
                form.append('fileData', file.slice(offset * LIMIT , (offset + 1) * LIMIT));
                // 配合后台逻辑判断是否完结
                form.append('isFinished' , file.size - start > LIMIT ? false : true);
                // 保存名字
                form.append('name' , file.name);
                // 这里可以用来记录文件已经成功上传的文件块，可以用来断点续传
                form.append('currBlock' , start);
                $.ajax({
                    url: url,
                    type: 'POST',
                    cache: false,
                    data: form,
                    processData: false,
                    contentType: false
                }).done(function(res) {
                    console.log(res);
                }).fail(function(res) {
                    console.log(res);
                });
                start += LIMIT;
                offset ++ ;
            }
        }
        $('#upload').change(function(){
            var file = $(this).prop('files')[0];
            startUpload(file);
        });
    });
```
分割过程比较简单，其中主要的一个方法是用到了`slice`，该方法提供两个参数，分别是游标位置和要切割的长度，这个方法会把文件切割成我们规定大小的一个`Blob`文件块，这个对象存储的是文件的原始二进制数据，切割100个字节的结果`console.log(file.slice(0 , 100))`:

![9c826d17-47cc-422b-bc9d-7e156b096230.png][2]
而`FormData`是`H5`提供的一个`Api`，可以让`JS`自主上传，这里我们添加了一个`currBlock`数据，虽然在我们的`Demo`中没有使用到，但是在真正的开发环境中，我们一般会提供一个断点续传功能，因此这个就派上了用场。

2. 合并文本
合并文本的工作就非常简单：
```
file_put_contents($fileName , file_get_contents($file['tmp_name']) , FILE_APPEND);
```
把传入的`Blob`直接追加到之前的文件里面即可，因为这里用`FILE_APPEND`会把指针直接移到文件最后，所以所占内存也比较小。

## 其他大文本操作

### 切割
其实对于大文本的切割我们也可以直接使用`Linux`的`split`来操作:
1. 按大小划分
```
split -b 10m file file_part_
```
`-b 10m`代表把文件按照`10m`的大小来划分区域，`file_part_`是我们划分的文件的前缀名字

![bef1bb84-a1bf-4020-8c9b-53d835668867.png][3]

2. 按行划分
```
split -l 100 file file_part_
```
`-l 100`代表以`100`行为一个节点来划分文件
![7a647477-9d21-4a5f-8773-c125087b4b0a.png][4]

3. 其他
```
split -a 4 -n 4 file file_part_
```
`-a 4`用来设置生成的名字的长度

![8811cdbb-b975-4d77-85ff-d17ad8424422.png][5]

### 合并
与`split`相对的，`Linux`也有一个`cat`指令来合并小文件:
```
cat file_part_* > file_part_test
```

### 读取
在开发中，如果遇到大文本的读取，一次性的全部读取不太现实，所以我们需要逐行或者逐块读取，`PHP`给我们提供了两个很好用的函数，分别是`fgets`，`fseek`:
`fgets`接受两个参数，第一个是文件句柄(handler)，第二个是我们需要读取的长度，默认是`1024`字节，当然遇到换行就会停止。
```
$handler = @fopen($file , 'r');
if($handler){
    while($buffer = fgets($handler , 2048) !== false){
// do something
    }
}
fclose($handler);
```
而`fseek`是用来操作文本指针的一个方法，该方法接受三个参数，第一个是文件句柄(handler)，第二个参数是偏移量，如果
成功返回`0`，失败返回`-1`:
```
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
```
输出:
```
0
0
48203
48303
100
```

## 参考
- [AJAX大文件切割上传(带进度条)](http://www.cnblogs.com/tlijian/p/3509215.html)
- [Linux split指令](https://blog.gtwang.org/linux/split-large-tar-into-multiple-files-of-certain-size/)


  [1]: http://www.hellonine.top/usr/uploads/2017/06/4221878456.png
  [2]: http://www.hellonine.top/usr/uploads/2017/06/518491670.png
  [3]: http://www.hellonine.top/usr/uploads/2017/06/3476542615.png
  [4]: http://www.hellonine.top/usr/uploads/2017/06/317429132.png
  [5]: http://www.hellonine.top/usr/uploads/2017/06/2902439357.png