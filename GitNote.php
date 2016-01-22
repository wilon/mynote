
git笔记

## 克隆一个项目，配置
git clone https://github.com/Wilon/mynote.git
git config --global alias.bak 'reset --hard HEAD~1'
git config --global alias.st status

## 查看一些信息
git status    // 项目目录里
git branch -a    // 查看所有分支，*代表本地
gti diff 文件    // 当期文件修改
git log
git show 4f84f230a9

## 添加文件
git add mynewsfile/*

## 提交到本地库
git commit -a -m '说明'    // 类vim选择，去掉#选择

## 从远程库拉取
git pull

## 提交到远程库
git push origin 库名

#回退所有内容到上一个版本
git reset --hard HEAD~1
git pull
git reset --hard HEAD~1

#重复的格式
<<<<<<< HEAD
        // if ($primaltpl) {
        //     $file = $_G['mobiletpl'][IN_MOBILE].'/'.$primaltpl;
        // }
=======
        if ($primaltpl) {
            $file = $_G['mobiletpl'][IN_MOBILE].'/'.$primaltpl;
        }
>>>>>>> 8178509b01536d6cc46362c5a159b97ee16a187e


SVN
# 提交代码
svn status    #查看状态
svn add    #添加文件
svn update    #向下更新，解决冲突
svn commit -m '说明'    #向上提交
# 查看一些信息
svn log    #查看提交日志
svn log -r 95    #查看某次提交日志


id_rsa
生成
linux
win GitTortoise
    PuTTYgen Generate/Load -> Save .. [key file]
    Pageant  右键 add key [key file]