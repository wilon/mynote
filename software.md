
### Lantern 更新 Host-Switch-Plus 代理
```shell
    Chrome 打开 Lantern 主页
    看[审查元素]：主页的 Remote Address 端口
    修改代理设置：PROXY 127.0.0.1:端口; DIRECT
```

### MacOS brew 安装PHP环境
```shell
    brew update    # 获取最新版brew
    brew search php
    brew install php56    # 安装
    brew remove mysql56 --force    # （强制）卸载
    brew upgrade nginx    # 更新
    brew options php56                 #查看php5.6安装选项
    brew info    php56                 #查看php5.6相关信息
    brew home    php56                 #访问php5.6官方网站
    brew services list                 #查看系统通过 brew 安装的服务
    brew services cleanup              #清除已卸载无用的启动配置文件
    brew services restart php56        #重启php-fpm
```

### MacOS 修改软件快捷键
```shell
    键盘 -> 应用快捷键 -> +
    菜单标题：应用程序菜单上文字
```

### VMware 设置某虚拟机开机启动
```shell
    \"VMware安装目录\\vmrun\" start \"虚拟机文件目录\\xxxx.vmx\" nogui > vmrun.cmd
    将vmrun.cmd放入开机启动目录startup\\
```

### Win 命令提示行美化
```shell
    1. 属性 -> 字体大小调为36
```

### 破解工具
```html
    # SQLyog
    1. 永久试用：HKEY_CURRENT_USER\\SOFTWARE 对应的权限锁死；
    2. 字体改为Consolas,14px；
    # Datagrip
    <a href="http://idea.lanyus.com/">http://idea.lanyus.com/</a>
    <a href="https://hub.docker.com/r/woailuoli993/jblse/">https://hub.docker.com/r/woailuoli993/jblse/</a>
```

### Sublime Text 更换package包源
```shell
    打开命令面板 Ctrl(Command)+Shift+p
    找到并打开：Preferences: Package Control sublime Settings - User
    添加一行"channels"字段：

    "channels":
    [
    	"http://wilon.github.io/static/channel_v3.json"
    ],

```

### Atom 更换 npm 源
```shell
    ~/.atom/.apmrc 文件添加一行： registry = https://registry.npm.taobao.org
```

### Atom 必备插件
```shell
    activate-power-mode  # 敲代码动态炫效果 —— SETTING: 屏幕关，按键关；CONTROL: ActivatePowerPode-Toggle
    pigments  # 文件颜色即时显示
    minimap # 代码缩略图
```
