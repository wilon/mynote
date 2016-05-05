
git笔记

id_rsa公钥
id_rsa.pub私钥


## 克隆一个项目
git clone https://github.com/Wilon/mynote.git
## 命令起别名
git config --global alias.bak 'reset --hard HEAD~1'
git config --global alias.st status
git config --global alias.ll 'log --graph'

## 查看一些信息
git status    # 项目目录里
git branch -a    # 查看所有分支，*代表本地
gti diff 文件    # 当期文件修改
git log    # 查看提交日志
git log -p    # 查看提交日志，包含代码
git log --graph    # 以图表形式查看分支提交日志
git show    # 查看最近一次提交代码
git show commit_id   # 查看某一次提交代码

## 添加文件
git add mynewsfile/*

## 提交到本地库
git commit -a -m '说明'    # 类vim选择，去掉#选择

## 从远程库拉取
git pull

## 提交到远程库
git push origin 库名

# 回退所有内容到上一个版本
git reset --hard HEAD~1
git pull
git reset --hard HEAD~1

# git分支
$ git branch <branchname>

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
