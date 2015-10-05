<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>


        <title>Discuz!接口</title>
        <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
        <script src="./jquery-1.7.2.min.js"></script>
        <base target="_blank" />
        <style type="text/css">
            body,td,th
            {
                font-size:12px;
            }
            ul
            {
                font-size:14px;
            }
            tt{ font-weight:bold;}
            .pad-left-150{
                padding-left:150px;
            }
            .pad-left-100{
                padding-left:100px;
            }
            .pad-left-50{
                padding-left:50px;
            }
            .unsure{
                border:1px solid red;
            }
            div.panel,p.flip{
                margin:0px;
                padding:5px;
                text-align:center;
                background:#e5eecc;
                border:solid 1px #c3c3c3;
            }
        </style>
        <script type="text/javascript">
            function show(obj) {
                var $jquery = $(obj)
                $jquery.parent().parent().parent().parent().next(".panel").slideToggle("slow");
            }
        </script>
    </head>
    <body>
        <div>
            <tt>Discuz!接口说明</tt>
            <ul>
                <li>适用<font color="red">Discuz!_X3.1_GBK</font></li>
                <li>所有接口采用POST方式传参</li>
                <li>返回的结果是json数据</li>
            </ul>
        </div>
        <hr />
        <div>
            <tt>1. 版块列表 （./t_forum_list.php）POST</tt>
                <table>
                    <form action="./t_forum_list.php" method="post">
                        <tr>
                            <td width="150px">版块父ID(gid)</td>
                            <td><input type="text" name="gid" value="" /></td>
                        </tr>
                        <!-- <tr>
                            <td width="150px">用户ID(uid)</td>
                            <td><input type="text" name="uid" value="2" /></td>
                        </tr> -->
                        <tr>
                            <td><input type="submit" value=" 提交 " /></td>
                            <td><p class="flip" onclick="javascript:show(this)">频道参数</p></td>
                        </tr>
                    </form>
                </table>
    <div class="panel" style="width:625px;display:none;">
        <table width="625px" border="1px" cellspacing="0px" cellpadding="0px">
            <tr height="60">
                <td align="right" width="40%">传入参数：</td>
                <td width="60%">
                    <table width="100%" border="1px" cellspacing="0px" cellpadding="0px">
                        <tr>
                            <td width="20%">参数</td>
                            <td width="20%">是否必须</td>
                            <td width="80%">描述</td>
                        </tr>
                        <tr>
                            <td>gid</td>
                            <td>是</td>
                            <td>版块父ID
                            </td>
                        </tr>
                        <!-- <tr>
                            <td>uid</td>
                            <td>否</td>
                            <td>用户ID
                            </td>
                        </tr> -->
                    </table>
                </td>
            </tr>
            <tr>
                <td align="right">返回文本样式:</td>
                <td align="left"><textarea cols="55" rows="25" readonly="readonly">
{
    "msg": "OK",
    "data": [
        {
            "fid": "39",
            "name": "加州",
            "icon": "",
            "threadtypes": {
                "required": true,
                "types": {
                    "1": "吃",
                    "2": "喝"
                }
            }
        },
        {
            "fid": "40",
            "name": "德州",
            "icon": "",
            "threadtypes": {
                "required": null,
                "types": null
            }
        }
    ],
    "code": 20000
}
                    </textarea></td></tr>
            <tr>
                <td align="right" width="40%">返回参数：</td>
                <td align="left" width="60%">
                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                        <tr><td align="left">data</td><td>返回数据</td></tr>
                        <tr><td class="pad-left-50">fid</td><td>板块ID号</td></tr>
                        <tr><td class="pad-left-50">name</td><td>板块名称</td></tr>
                        <tr><td class="pad-left-50">icon</td><td>板块图标</td></tr>
                        <tr><td class="pad-left-50">threadtypes</td><td>板块名颜色</td></tr>
                        <tr><td class="pad-left-100">required</td><td>板块分类是否必须，用户发帖</td></tr>
                        <tr><td class="pad-left-100">types</td><td>板块分类，键+值=>分类ID+分类名称</td></tr>
                        <tr><td align="left">msg</td><td>响应回复信息</td></tr>
                        <tr><td align="left">code</td><td>状态码，20000成功</td></tr>
                    </table></td>
            </tr></table>
    </div>
        </div>
        <hr />
        <div>
            <tt>2. 主题列表 （./t_forum_postlist.php）POST</tt>
                <table>
                    <form action="./t_forum_postlist.php" method="post">
                        <tr>
                            <td width="150px">版块ID(fid)</td>
                            <td><input type="text" name="fid" value="" /></td>
                        </tr>
                        <tr>
                            <td width="150px">页码(page)</td>
                            <td><input type="text" name="page" value="1" /></td>
                        </tr>
                        <tr>
                            <td width="150px">每页条数(pagesize)</td>
                            <td><input type="text" name="pagesize" value="20" /></td>
                        </tr>
                        <!-- <tr>
                            <td width="150px">用户ID(uid)</td>
                            <td><input type="text" name="uid" value="2" /></td>
                        </tr> -->
                        <tr>
                            <td><input type="submit" value=" 提交 " /></td>
                            <td><p class="flip" onclick="javascript:show(this)">频道参数</p></td>
                        </tr>
                    </form>
                </table>
    <div class="panel" style="width:625px;display:none;">
        <table width="625px" border="1px" cellspacing="0px" cellpadding="0px">
            <tr height="60">
                <td align="right" width="40%">传入参数：</td>
                <td width="60%">
                    <table width="100%" border="1px" cellspacing="0px" cellpadding="0px">
                        <tr>
                            <td width="20%">参数</td>
                            <td width="20%">是否必须</td>
                            <td width="80%">描述</td>
                        </tr>
                        <tr>
                            <td>fid</td>
                            <td>是</td>
                            <td>版块ID
                            </td>
                        </tr>
                        <tr>
                            <td>page</td>
                            <td>否</td>
                            <td>页码
                            </td>
                        </tr>
                        <tr>
                            <td>pagesize</td>
                            <td>否</td>
                            <td>每条页数
                            </td>
                        </tr>
                        <!-- <tr>
                            <td>uid</td>
                            <td>否</td>
                            <td>用户ID
                            </td>
                        </tr> -->
                    </table>
                </td>
            </tr>
            <tr>
                <td align="right">返回文本样式:</td>
                <td align="left"><textarea cols="55" rows="27" readonly="readonly">
{
    "msg": "OK",
    "data": [
        {
            "tid": "133",
            "fid": "37",
            "author": "333",
            "authorid": "2",
            "subject": "九块九大拍卖，不买也看看啊~",
            "dateline": "1411098219",
            "displayorder": "2",
            "special": "0",
            "views": "40",
            "replies": "8"
        },
        {
            "tid": "158",
            "fid": "39",
            "author": "admin",
            "authorid": "1",
            "subject": "九块九大拍卖，不买也看看啊~",
            "dateline": "1412067654",
            "displayorder": "0"
            "special": "1",
            "views": "20",
            "replies": "5"
        },
        ...
    ],
    "code": 20000
}
                    </textarea></td></tr>
            <tr>
                <td align="right" width="40%">返回参数：</td>
                <td align="left" width="60%">
                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                        <tr><td align="left">data</td><td>返回数据</td></tr>
                        <tr><td class="pad-left-50">tid</td><td>主题ID</td></tr>
                        <tr><td class="pad-left-50">fid</td><td>主题所属板块ID</td></tr>
                        <tr><td class="pad-left-50">author</td><td>发帖用户名</td></tr>
                        <tr><td class="pad-left-50">authorid</td><td>发帖用户ID</td></tr>
                        <tr><td class="pad-left-50">subject</td><td>主题标题</td></tr>
                        <tr><td class="pad-left-50">dateline</td><td>主题发布时间</td></tr>
                        <tr><td class="pad-left-50">displayorder</td><td>主题显示顺序 （3三级置顶 2二级置顶 1一级置顶 0正常）</td></tr>
                        <tr><td class="pad-left-50">special</td><td>特殊主题（0普通 1投票）</td></tr>
                        <tr><td class="pad-left-50">views</td><td>主题查看数</td></tr>
                        <tr><td class="pad-left-50">replies</td><td>主题回复数</td></tr>
                        <tr><td align="left">msg</td><td>响应回复信息</td></tr>
                        <tr><td align="left">code</td><td>状态码，20000成功</td></tr>
                    </table></td>
            </tr></table>
    </div>
        </div>
        <hr />
        <div>
            <tt>3. 帖子详情 （./t_forum_viewthread.php）POST</tt>
                <table>
                    <form action="./t_forum_viewthread.php" method="post">
                        <tr>
                            <td width="150px">主题ID(tid)</td>
                            <td><input type="text" name="tid" value="" /></td>
                        </tr>
                       <!--  <tr>
                            <td width="150px">用户ID(uid)</td>
                            <td><input type="text" name="uid" value="2" /></td>
                        </tr> -->
                        <tr>
                            <td><input type="submit" value=" 提交 " /></td>
                            <td><p class="flip" onclick="javascript:show(this)">频道参数</p></td>
                        </tr>
                    </form>
                </table>
    <div class="panel" style="width:625px;display:none;">
        <table width="625px" border="1px" cellspacing="0px" cellpadding="0px">
            <tr height="60">
                <td align="right" width="40%">传入参数：</td>
                <td width="60%">
                    <table width="100%" border="1px" cellspacing="0px" cellpadding="0px">
                        <tr>
                            <td width="20%">参数</td>
                            <td width="20%">是否必须</td>
                            <td width="80%">描述</td>
                        </tr>
                        <tr>
                            <td>tid</td>
                            <td>是</td>
                            <td>版块ID
                            </td>
                        </tr>
                        <tr>
                            <td>page</td>
                            <td>否</td>
                            <td>页码
                            </td>
                        </tr>
                        <!-- <tr>
                            <td>uid</td>
                            <td>否</td>
                            <td>用户ID
                            </td>
                        </tr> -->
                    </table>
                </td>
            </tr>
            <tr>
                <td align="right">返回文本样式:</td>
                <td align="left"><textarea cols="55" rows="27" readonly="readonly">
{
    "msg": "ok",
    "data": [
        {
            "pid": "64",
            "fid": "39",
            "tid": "64",
            "author": "admin",
            "authorid": "1",
            "subject": "九块九大拍卖，不买也看看啊~",
            "dateline": "2014-9-18 17:41:03",
            "message": "九块九大拍卖，不买也看看啊~九块九大拍卖，不买也看看啊~九块九大拍卖，不买也看看啊~<br />\r\n&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp; "
        },
        {
            "pid": "147",
            "fid": "39",
            "tid": "64",
            "author": "admin",
            "authorid": "1",
            "subject": "",
            "dateline": "2014-9-30 15:47:49",
            "message": "&lt;a&gt;<a href=\"http://www.taizhou.com/appapi/bbbbb.com\" target=\"_blank\">bbbb</a>"
        }
    ],
    "code": 20000
}
                    </textarea></td></tr>
            <tr>
                <td align="right" width="40%">返回参数：</td>
                <td align="left" width="60%">
                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                        <tr><td align="left">data</td><td>返回数据</td></tr>
                        <tr><td class="pad-left-50">pid</td><td>帖子ID</td></tr>
                        <tr><td class="pad-left-50">tid</td><td>主题ID</td></tr>
                        <tr><td class="pad-left-50">fid</td><td>帖子所属板块ID</td></tr>
                        <tr><td class="pad-left-50">author</td><td>发帖用户名</td></tr>
                        <tr><td class="pad-left-50">authorid</td><td>发帖用户ID</td></tr>
                        <tr><td class="pad-left-50">subject</td><td>帖子标题</td></tr>
                        <tr><td class="pad-left-50">dateline</td><td>帖子发布时间</td></tr>
                        <tr><td class="pad-left-50">message</td><td>帖子内容</td></tr>
                        <tr><td align="left">msg</td><td>响应回复信息</td></tr>
                        <tr><td align="left">code</td><td>状态码，20000成功</td></tr>
                    </table></td>
            </tr></table>
    </div>
        </div>
        <hr />
        <div>
            <tt>4. 发布主题 （./t_forum_newthread.php）POST</tt>
                <table>
                    <form action="./t_forum_newthread.php?action=newthread" method="post">
                        <tr>
                            <td width="150px">版块ID(fid)</td>
                            <td><input type="text" name="fid" value="" /></td>
                        </tr>
                        <tr>
                            <td width="150px">版块类型ID(typeid)</td>
                            <td><input type="text" name="typeid" value="" /></td>

                        </tr>
                        <tr>
                            <td width="150px">用户ID(uid)</td>
                            <td><input type="text" name="uid" value="100440" /></td>
                        </tr>
                        <tr>
                            <td width="150px">标题(subject)</td>
                            <td><input type="text" name="subject" value="九块九大拍卖，不买也看看啊~" /></td>
                        </tr>
                        <tr>
                            <td width="150px">内容(message)</td>
                            <td><textarea name="message" rows="6" cols="44">
九块九大拍卖，不买也看看啊~九块九大拍卖，不买也看看啊~九块九大拍卖，不买也看看啊~
                            </textarea>
                            </td>
                        </tr>
                        <tr>
                            <td width="150px">图片(pic)</td>
                            <td><textarea name="pic" rows="6" cols="44">
/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAIBAQIBAQICAgICAgICAwUDAwMDAwYEBAMFBwYHBwcGBwcICQsJCAgKCAcHCg0KCgsMDAwMBwkODw0MDgsMDAz/2wBDAQICAgMDAwYDAwYMCAcIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAz/wAARCAAWAB4DASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9H/iP498L/s1/AO+vPHV1D4P+GHhfTZLzU72d9qx242r5W1Q0sjPu2IifO7MkYOWUV+ZmgfF/45f8FHPEEn7Sng34la74V8HWUt0ngj4f6ZrtzpdxZi2u/slrBND/AMeN9PduN00Tl3nMyW4AhWNV94/4LL/sw+NP2s7jwn4f+3WNj4b1PxFaW9tFeSEaYMxg3F/KNwSZoYWmlGX3CCG6WOPcjGX8fdN8Q6lqvx7uR8Ib64bwzHr1zB4VsdU8prRdLjlW3he7h2tC7tDHD5wIfepCkEJivLyTB1cdU+rp2d+iu7dNNL3f+R6mYYmll9L201zK2ibsr+vkkf0mftIfEf4f/BLwD4g8YeNb7R/D/gvwzfKlzea1CZLFFluI4IZGjCszMZJIl+UEgOTwMmvE9K+JngP9pX4axeOvh3401RPC2oapNZteaVbahZQvdJGkkiRG7giaeH96SsyIUOCm9jEQPG/2Hv2Gb741aXo/ir4ofHjT/izDrFrcDUPCev6JPd2NncvPFKDEbi98tpobi3T50g2soT5QyRsv6D+HPBGrPalvEeqafrOofde4t7f7PHJjpiNmfYADgKGI64wMCuWtGjKcqMJqai2m1pqt9FezT0tcqlzzpQqVFZyV7flrpfTyPjfxv4It4/2d7O806y0nxPrPhdxqnh2z8S2ytp8VpiVWtpiBIzbog5GVfY6xAEqmK/DX4ZeP76x/aY1jVtSkhi1NtUn1e5OmWy29tHNJcebKIIQQsSZb5UHCgKowBmiiuPwMxlarJyqSu+e13vayObxAow9g7Loz7x/4Js/tZ6HD+3tqvhHQLTxBo8nijW5oNZsvNMmk6glxpl5qrXCRGUi1uYprCeLFsscFxFfJvije0V5+8+F//Bcf43/s4fGv4ifCTxFp/gn4jW3w71zUdB0/Vr+K5tdRnjsb+ey3XEiyMszFYozv8tGJLFiTySivQx2X4d8Y4uly2ThGWl1rzzV7qz2SXyRyYDEVY5JhpJ67d9OWPc//2Q==
                            </textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="submit" value=" 提交 " /></td>
                            <td><p class="flip" onclick="javascript:show(this)">频道参数</p></td>
                        </tr>
                    </form>
                </table>
    <div class="panel" style="width:625px;display:none;">
        <table width="625px" border="1px" cellspacing="0px" cellpadding="0px">
            <tr height="60">
                <td align="right" width="40%">传入参数：</td>
                <td width="60%">
                    <table width="100%" border="1px" cellspacing="0px" cellpadding="0px">
                        <tr>
                            <td width="20%">参数</td>
                            <td width="20%">是否必须</td>
                            <td width="80%">描述</td>
                        </tr>
                        <tr>
                            <td>fid</td>
                            <td>是</td>
                            <td>版块ID</td>
                        </tr>
                        <tr>
                            <td>typeid</td>
                            <td>通过第一个接口查找fid，若threadtypes[required] = true，则此接口id必须，且id号为threadtypes[types]的键。否则不必填写</td>
                            <td>版块类型ID</td>
                        </tr>
                        <tr>
                            <td>uid</td>
                            <td>是</td>
                            <td>用户ID</td>
                        </tr>
                        <tr>
                            <td>subject</td>
                            <td>是</td>
                            <td>标题</td>
                        </tr>
                        <tr>
                            <td>subject</td>
                            <td>是</td>
                            <td>标题</td>
                        </tr>
                        <tr>
                            <td>message</td>
                            <td>是</td>
                            <td>内容</td>
                        </tr>
                        <tr>
                            <td>pics</td>
                            <td>否</td>
                            <td>图片</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="right">返回文本样式:</td>
                <td align="left"><textarea cols="55" rows="6" readonly="readonly">
{
    "data": 78,
    "msg": "OK",
    "code": 20000
}
                    </textarea></td></tr>
            <tr>
                <td align="right" width="40%">返回参数：</td>
                <td align="left" width="60%">
                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                        <tr><td align="center">data</td><td>返回数据：帖子pid</td></tr>
                        <tr><td align="center">msg</td><td>响应回复信息</td></tr>
                        <tr><td align="center">code</td><td>状态码，20000评论成功</td></tr>
                    </table></td>
            </tr></table>
    </div>
        </div>
        <hr />
        <div>
            <tt>5. 回复帖子 （./t_forum_replythread.php）POST</tt>
                <table>
                    <form action="./t_forum_replythread.php?action=reply" method="post">
                        <tr>
                            <td width="150px">主题ID(tid)</td>
                            <td><input type="text" name="tid" value="" /></td>
                        </tr>
                        <tr>
                            <td width="150px">用户ID(uid)</td>
                            <td><input type="text" name="uid" value="100440" /></td>
                        </tr>
                        <tr>
                            <td width="150px">内容(message)</td>
                            <td><textarea name="message" rows="6" cols="44">
近日骗子又出新招，电话恐吓事主涉嫌洗钱后，再诱骗受害者访问假冒的检察院网站，下载 “犯罪清查系统”。360安全卫士提醒：所谓“犯罪清查系统”是骗子定制的Teamviewer远程监控软件，安装使用后电脑会被对方完全控制，骗子再趁机盗刷网银！
                            </textarea>
                            </td>
                        </tr>
                        <tr>
                            <td width="150px">图片(pic)</td>
                            <td><textarea name="pic" rows="6" cols="44">
/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAIBAQIBAQICAgICAgICAwUDAwMDAwYEBAMFBwYHBwcGBwcICQsJCAgKCAcHCg0KCgsMDAwMBwkODw0MDgsMDAz/2wBDAQICAgMDAwYDAwYMCAcIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAz/wAARCAAWAB4DASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9H/iP498L/s1/AO+vPHV1D4P+GHhfTZLzU72d9qx242r5W1Q0sjPu2IifO7MkYOWUV+ZmgfF/45f8FHPEEn7Sng34la74V8HWUt0ngj4f6ZrtzpdxZi2u/slrBND/AMeN9PduN00Tl3nMyW4AhWNV94/4LL/sw+NP2s7jwn4f+3WNj4b1PxFaW9tFeSEaYMxg3F/KNwSZoYWmlGX3CCG6WOPcjGX8fdN8Q6lqvx7uR8Ib64bwzHr1zB4VsdU8prRdLjlW3he7h2tC7tDHD5wIfepCkEJivLyTB1cdU+rp2d+iu7dNNL3f+R6mYYmll9L201zK2ibsr+vkkf0mftIfEf4f/BLwD4g8YeNb7R/D/gvwzfKlzea1CZLFFluI4IZGjCszMZJIl+UEgOTwMmvE9K+JngP9pX4axeOvh3401RPC2oapNZteaVbahZQvdJGkkiRG7giaeH96SsyIUOCm9jEQPG/2Hv2Gb741aXo/ir4ofHjT/izDrFrcDUPCev6JPd2NncvPFKDEbi98tpobi3T50g2soT5QyRsv6D+HPBGrPalvEeqafrOofde4t7f7PHJjpiNmfYADgKGI64wMCuWtGjKcqMJqai2m1pqt9FezT0tcqlzzpQqVFZyV7flrpfTyPjfxv4It4/2d7O806y0nxPrPhdxqnh2z8S2ytp8VpiVWtpiBIzbog5GVfY6xAEqmK/DX4ZeP76x/aY1jVtSkhi1NtUn1e5OmWy29tHNJcebKIIQQsSZb5UHCgKowBmiiuPwMxlarJyqSu+e13vayObxAow9g7Loz7x/4Js/tZ6HD+3tqvhHQLTxBo8nijW5oNZsvNMmk6glxpl5qrXCRGUi1uYprCeLFsscFxFfJvije0V5+8+F//Bcf43/s4fGv4ifCTxFp/gn4jW3w71zUdB0/Vr+K5tdRnjsb+ey3XEiyMszFYozv8tGJLFiTySivQx2X4d8Y4uly2ThGWl1rzzV7qz2SXyRyYDEVY5JhpJ67d9OWPc//2Q==
                            </textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="submit" value=" 提交 " /></td>
                            <td><p class="flip" onclick="javascript:show(this)">频道参数</p></td>
                        </tr>
                    </form>
                </table>
                <div class="panel" style="width:625px;display:none;">
                    <table width="625px" border="1px" cellspacing="0px" cellpadding="0px">
                        <tr height="60">
                            <td align="right" width="40%">传入参数：</td>
                            <td width="60%">
                                <table width="100%" border="1px" cellspacing="0px" cellpadding="0px">
                                    <tr>
                                        <td width="20%">参数</td>
                                        <td width="20%">是否必须</td>
                                        <td width="80%">描述</td>
                                    </tr>
                                    <tr>
                                        <td>tid</td>
                                        <td>是</td>
                                        <td>主题ID</td>
                                    </tr>
                                    <tr>
                                        <td>uid</td>
                                        <td>是</td>
                                        <td>用户ID</td>
                                    </tr>
                                    <tr>
                                        <td>message</td>
                                        <td>是</td>
                                        <td>回复内容</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">返回文本样式:</td>
                            <td align="left"><textarea cols="55" rows="6" readonly="readonly">
{
    "data": 125,
    "msg": "OK",
    "code": 20000
}
                    </textarea></td></tr>
            <tr>
                <td align="right" width="40%">返回参数：</td>
                <td align="left" width="60%">
                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                        <tr><td align="center">data</td><td>返回数据：帖子pid</td></tr>
                        <tr><td align="center">msg</td><td>响应回复信息</td></tr>
                        <tr><td align="center">code</td><td>状态码，20000评论成功</td></tr>
                    </table></td>
                        </tr></table>
                </div>
        </div>
        <!-- <div>
        <hr />
            <tt>6. 添加帖子图片 （./t_forum_threadimg.php）POST</tt>
                <table>
                    <form action="./t_forum_threadimg.php" method="post" enctype="multipart/form-data">
                        <tr>
                            <td width="150px">帖子ID(pid)</td>
                            <td><input type="text" name="pid" value="64" /></td>
                        </tr>
                        <tr>
                            <td width="150px">文件(file)</td>
                            <td><input type="file" name="file" value="" /></td>
                        </tr>
                        <tr>
                            <td><input type="submit" value=" 提交 " /></td>
                            <td><p class="flip" onclick="javascript:show(this)">频道参数</p></td>
                        </tr>
                    </form>
                </table>
                <div class="panel" style="width:625px;display:none;">
                    <table width="625px" border="1px" cellspacing="0px" cellpadding="0px">
                        <tr height="60">
                            <td align="right" width="40%">传入参数：</td>
                            <td width="60%">
                                <table width="100%" border="1px" cellspacing="0px" cellpadding="0px">
                                    <tr>
                                        <td width="20%">参数</td>
                                        <td width="20%">是否必须</td>
                                        <td width="80%">描述</td>
                                    </tr>
                                    <tr>
                                        <td>pid</td>
                                        <td>是</td>
                                        <td>帖子ID</td>
                                    </tr>
                                    <tr>
                                        <td>file</td>
                                        <td>是</td>
                                        <td>上传文件</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">返回文本样式:</td>
                            <td align="left"><textarea cols="55" rows="6" readonly="readonly">
{
    "data": "",
    "msg": "保存图片成功",
    "code": 20000
}
                    </textarea></td></tr>
            <tr>
                <td align="right" width="40%">返回参数：</td>
                <td align="left" width="60%">
                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                        <tr><td align="center">data</td><td>返回数据</td></tr>
                        <tr><td align="center">msg</td><td>响应回复信息</td></tr>
                        <tr><td align="center">code</td><td>状态码，20000评论成功</td></tr>
                    </table></td>
                        </tr></table>
                </div>
        </div>	 -->
    </body>
</html>

