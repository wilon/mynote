
### zsh + oh-my-zsh
```shell
    yum install zsh
    sh -c "$(curl -fsSL https://raw.githubusercontent.com/wilon/oh-my-zsh/master/tools/install.sh)"
    chsh -s /bin/zsh    # 修改默认zsh，需重启。或修改 /etc/passwd
    zsh    # 手动切换
```

### nodejs + npm + gulp
```shell
    # install
    yum -y install nodejs
    yum -y install npm
    npm install gulp -g    # global
    npm install gulp --save    # save to package.json
    # Ubuntu install last nodejs
    curl -sL https://deb.nodesource.com/setup_6.x | sudo bash -    # source
    sudo apt-get install nodejs
    # other
    npm config set registry https://registry.npm.taobao.org    # taobao
    sudo ln -s /usr/bin/nodejs /usr/bin/node    # Ubuntu
```

### PHP help
```shell
    # path
    php -i | grep configure    # 可以查看【PHP安装目录】位置
    php -i | grep php.ini    # 可以查看【php.ini】位置
    ps aux | grep php-fpm.conf    # 可以查看【php-fpm.conf】位置
    php-fpm -t    # 可以查看【php-fpm.conf】位置

    export PATH=$PATH:PHP安装目录/php/bin    # php命令加入path
    # confi 配置文件
    php -v
    # test
    wget https://wilon.github.io/static/p.php    # 雅黑PHP探针
```

### other
```shell
    echo $PATH    # 查看可以PATH，按优先级排列
    echo 'aaaaaa' > test.htm
    echo -n 'aaaaaa' > test.htm    # 没有换行
```

### top 命令详解 help
```shell
    第一行，任务队列信息 — 当前系统时间 — 系统运行时间 — 当前用户登录数 - 负载情况
    第三行，CPU状态信息 - 用户空间占用CPU的百分比 — 内核空间占用CPU的百分比, id — 空闲CPU百分比
    第四行，内存状态 - 物理内存总量 — 使用中的内存总量 — 空闲内存总量 — 缓存的内存量    # 8,000,000 = 8G
    第五行，SWAP交换分区信息 — 交换区总量 — 使用的交换区总量 — 空闲交换区总量 — 缓冲的交换区总量
    第七行以下：各进程（任务）的状态监控
        PID — 进程id
        USER — 进程所有者
        PR — 进程优先级
        NI — nice值。负值表示高优先级，正值表示低优先级
        VIRT — 进程使用的虚拟内存总量，单位kb。VIRT=SWAP+RES
        RES — 进程使用的、未被换出的物理内存大小，单位kb。RES=CODE+DATA
        SHR — 共享内存大小，单位kb
        S — 进程状态。D=不可中断的睡眠状态 R=运行 S=睡眠 T=跟踪/停止 Z=僵尸进程
        %CPU — 上次更新到现在的CPU时间占用百分比
        %MEM — 进程使用的物理内存百分比
        TIME+ — 进程使用的CPU时间总计，单位1/100秒
        COMMAND — 进程名称（命令名/命令行）
```

### docker help
```shell
    service docker start    # 启动服务
    # docker CONTAINER 镜像
    docker pull <REPOSITORY>
    docker images    #  查看安装的镜像
    docker run <REPOSITORY> <COMMAND>    # 在容器内运行镜像
    # docker CONTAINER 容器
    docker ps -a    # 查看所有容器
    docker start <CONTAINER ID>    # 开始该容器
    docker stop <CONTAINER ID>    # 停止该容器
    docker rm <CONTAINER ID>    # 删除该容器
```

### 定时任务crontab
```shell
    # 安装配置
    yum -y install vixie-cron    # 软件包是cron的主程序；
    yum -y install crontabs    # 软件包是用来安装、卸装、或列举用来驱动cron守护进程的表格的程序。
    service crond start    # 启动服务
    chkconfig --level 345 crond on    # 配置开机启动
    vim  /etc/crontab    # 配置文件
    # 定时任务： 分　时　日　月　周　 命令
    30 21 * * * /usr/local/etc/rc.d/lighttpd restart    # 每晚的21:30重启apache。
    45 4 1,10,22 * * /usr/local/etc/rc.d/lighttpd restart    # 每月1、10、22日的4 : 45重启apache。
    10 1 * * 6,0 /usr/local/etc/rc.d/lighttpd restart    # 每周六、周日的1 : 10重启apache。
    0,30 18-23 * * * /usr/local/etc/rc.d/lighttpd restart    # 在每天18 : 00至23 : 00之间每隔30分钟重启apache。
    0 23 * * 6 /usr/local/etc/rc.d/lighttpd restart    # 每星期六的11 : 00 pm重启apache。
    * */1 * * * /usr/local/etc/rc.d/lighttpd restart    # 每一小时重启apache
    * 23-7/1 * * * /usr/local/etc/rc.d/lighttpd restart    # 晚上11点到早上7点之间，每隔一小时重启apache
    0 11 4 * mon-wed /usr/local/etc/rc.d/lighttpd restart    # 每月的4号与每周一到周三的11点重启apache
    0 4 1 jan * /usr/local/etc/rc.d/lighttpd restart    # 一月一号的4点重启apache
    */30 * * * * /usr/sbin/ntpdate 210.72.145.44    # 每半小时同步一下时间
```

### 重启
```shell
    # 重启电脑
    shutdown -h|-r now    # 安全关机|重启，now可以更改为时间
    reboot    # 重启
    # 重启\服务：
    /usr/local/apache2/bin/apachectl stop
    /usr/local/apache2/bin/apachectl start
    /etc/rc.d/init.d/nginx restart
    service nginxd reload
```

### service
```shell
    # 能识别的目录！直接service httpd start
    /etc/rc.d/init.d/
```

### 时间及管理
```shell
    date    # 查看系统时间
    date -s 20130220    # 设定日期
    date -s 09:30:00    # 设定时间
    # 远程校准时间
    yum -y install ntpdate
    ntpdate cn.pool.ntp.org
```

### php进程管理php-fpm
```shell
    # 1. 查看服务
    ps aux | grep --color=auto php-fpm
    # 2. 修改执行php进程用户
    vim /etc/php-fpm.d/www.conf     # 修改 user group
    /etc/init.d/php-fpm restart    # 重启
    chown -R user:group /var/lib/php/session    # 修改需要权限的文件夹
```

### 常见目录信息
```shell
    /           # 根目录
    /bin        # 命令保存目录（普通用户就可以读取的命令）
    /boot       # 启动目录，启动相关文件
    /dev        # 设备文件保存目录
    /etc        # 配置文件保存目录
    /home       # 普通用户的家目录
    /lib        # 系统库保存目录
    /mnt        # 系统挂载目录
    /media      # 挂载目录
    /root       # 超级用户的家目录
    /tmp        # 临时目录
    /sbin       # 命令保存目录（超级用户才能使用的目录）
    /proc       # 直接写入内存的
    /sys
    /usr                # 系统软件资源目录
    /usr/bin/           # 系统命令（普通用户）
    /usr/sbin/          # 系统命令（超级用户）
    /var                # 系统相关文档内容（系统可变数据保存目录）
    /var/log/           # 系统日志位置
    /var/spool/mail/    # 系统默认邮箱位置
    /var/lib/mysql/     # 默认安装的mysql的库文件目录
```

### 查看系统信息
```shell
    netstat -apn | grep 80    # 查看80端口被哪些进程占用
    ps aux[|grep nginx]    # 查看当前系统所有运行的进程
    uname -a    # 内核版本
    cat /etc/issue    # 系统信息
    cat /proc/version    # 系统详情
```

### 查看文件夹信息
```shell
    df -h    # 查看磁盘空间
    du -sh *    # 查看当前目录下个文件（夹）大小
    du -sh * | sort -rn | grep "M\s"    # sort
    ls | wc -l    # 查看当前文件夹下文件（夹）的个数
    ls -l | grep "^-" | wc -l    # 查看当前文件夹下文件的个数
    ls -lR | grep "^-" | wc -l    # 查看某目录下文件的个数，包括子目录里的。
    ls -lR | grep "^d" | wc -l    # 查看某文件夹下目录的个数，包括子目录里的。
    ll --full-time    # 查看文件的完整时间信息
    ll -t | head -n 5    # 查看最新的5个文件
```

### composer安装
```shell
    curl -sS https://getcomposer.org/installer | php    # 下载源码包php执行
    mv composer.phar /usr/local/bin/composer    # 加入到系统命令
    composer config -g repo.packagist composer https://packagist.phpcomposer.com    # 全局配置国内镜像源
    composer config -l -g    # 查看全局配置信息
    composer clear-cache    # 清除缓存
    composer require --no-plugins --no-scripts xxx/xxxx     # root 下安装
```

### 安装php扩展extension
```shell
    # pear 命令安装
    pear install xdebug    # 失败则扩展pear已不维护
    # 源码安装
    cd /xxx/php-包/ext/EXTENSION
    phpize    # 确认命令可使用
    ./configure -with-php-config=/usr/local/php/bin/php-config
    make && make install
    echo extension=EXTENSION.so >> /usr/local/php/etc/php.ini
```

### 更换阿里云yum源
```shell
    mv /etc/yum.repos.d/CentOS-Base.repo /etc/yum.repos.d/CentOS-Base.repo.backup    # 备份
    wget -O /etc/yum.repos.d/CentOS-Base.repo http://mirrors.aliyun.com/repo/Centos-6.repo    # 下载相应的yum源
    yum clean all    # 运行yum
    yum makecache    # makecache生成缓存
```

### xshell连接服务器
```shell
    # 倒计时界面enter安装，一直下一步
    chkconfig iptables off    # 开机关闭iptables
    service iptables stop    # 立即关闭iptables
    # 设置网络连接为【NAT】
    service sshd start    # 开启ssh
```

### 服务开机启动
```shell
    vim /etc/inittab    # :id:5:initdefault: 启动级别，5图形界面改，3纯命令行
    chkconfig [--level 服务级别] 服务名 on    # 设置开机启动，off关闭
    chkconfig --level 345 mysqld on    # MySQL开机启动
    chkconfig --list    # 查看自启动列表、级别
    # 0:off  1:off   2:on    3:off   4:on    5:off   6:off
    ntsysv    # 伪图形界面启动服务
```

### 用户相关
```shell
    # 用户操作
    su weilong    # 切换用户
    sudo -i    # 切换到root
    useradd -G {group-name} weilong    # 新建用户[到组]
    # 密码操作
    passwd weilong    # 修改用户密码
    userdel [-r|f] weilong    # 删除用户[及目录|强制删除]
    vim /etc/passwd[group|shadow]    # 查看所有用户[组|密码]信息
    # 组操作
    gpasswd -a [-d] 用户名 组名    # 把用户加入[删除]到组
    usermod -a -G groupA user    # 将用户添加到组groupA中，而不必离开其他用户组
    # 给用户添加sudo，需root操作
    chmod 600 /etc/sudoers
    echo 'weilong ALL=(ALL) ALL' >> /etc/sudoers
    chmod 400 /etc/sudoers    # 收起写权限
    # 禁止用户登陆
    usermod -L weilong    # Lock 帐号weilong
    usermod -U weilong    # Unlock 帐号weilong
    # ssh秘钥登陆服务端配置：/etc/ssh/sshd_config
    RSAAuthentication yes    # 使用RSA认证
    PubkeyAuthentication yes    # 允许Pubkey Key
    AuthorizedKeysFile .ssh/authorized_keys    # id_rsa.pub放入【该用户】下此文件
    PasswordAuthentication no    # 不允许密码登陆
    PermitEmptyPasswords no    # 不允许无密码登陆
    PermitRootLogin no   # 不允许root直接登陆
    # sftp登陆服务器
    Subsystem sftp internal-sftp    # sftp配置
    X11Forwarding no
    AllowTcpForwarding no
    Match user[Group] weilong    # 配置用户[组]——start
    ForceCommand internal-sftp
    ChrootDirectory /home    # 所属用户必须为root——end
```

### 文件上传rz下载sz
```shell
    yum -y install lrzsz
```

### scp文件传输
```shell
    cp LOCAL_FILE REMOTE_USERNAME@REMOTE_IP:REMOTE_FOLDER
```

### 命令重命名，创建快捷命令
```shell
    echo alias ws=\\'cd /home/wwwroot/default/\\' >> ~/.bashrc && source ~/.bashrc
```

### 目录文件查找字符串grep
```shell
    grep [-acinv] [--color=auto] 'string/preg' FILENAME/FILEDIR
    -a # 将 binary 文件以 text 文件的方式搜寻数据
    -c # 计算找到 '搜寻字符串' 的次数
    -i # 忽略大小写的不同，所以大小写视为相同
    -n # 顺便输出行号
    -v # 反向选择，亦即显示出没有 '搜寻字符串' 内容的那一行！
    --color=auto # 可以将找到的关键词部分加上颜色的显示喔！
```

### 终端快捷操作
```shell
    ctrl+a 跳转至行首，ctrl+e 跳转至行尾
    ctrl+k 快清至行首，ctrl+u 快清至行尾
    ctrl+w 清除当前光标位置之前的一个单词
    ctrl+c 强制终止，ctrl+l 清屏
    cmd !! 双惊叹号表示上一行命令
    !cmd   执行最近的已cmd开头的命令
```

### 查看文件
```shell
    tail -f FILENAME    # 动态查看文件最新变化
    cat [-n] 文件名    # 所有内容[显示行号]
    more 文件名    # 分屏显示，space|b|q 翻页下|上|退出
    head [-n 2] 文件名    # 显示文件头[两行]
```
