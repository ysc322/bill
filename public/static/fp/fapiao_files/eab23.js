var yzmWait = 2;
var retrycount = 0;
$(document)["ready"](function () {
  $("#fpdm")["blur"](function () {
    retrycount = 0;
  });
});

function yzmTime(_0xb4c6d9) {
  if (yzmWait == 0) {
    $("#yzm_unuse_img")["hide"]();
    $("#yzm_img")["show"]();
    yzmWait = 60;
  } else {
    if (yzmWait == 2) {
      $("#yzm_unuse_img")["show"]();
      $("#yzm_img")["hide"]();
    }

    yzmWait--;
    setTimeout(function () {
      yzmTime(_0xb4c6d9);
    }, 1000);
  }
}

function getYzmXx() {
  $["pricode"]["clearA"]();
  show_yzm = "1";

  var _0x3f7257 = $("#fpdm")["val"]()["trim"]();

  var _0x5806c7 = getSwjg(_0x3f7257, 0);

  var _0x56bec6 = _0x5806c7[1] + "/yzmQuery";

  var _0x429990 = showTime()["toString"]();

  var _0x56f0eb = $("#fpdm")["val"]()["trim"]();

  var _0x43a40a = $("#fphm")["val"]()["trim"]();

  var _0x93eca2 = $("#kjje")["val"]()["trim"]();

  var _0x533c94 = Math["random"]();

  var _0x2649df = _0x5806c7[2];

  var bb =  $["nnyd"]["yzm"](_0x56f0eb, _0x43a40a, _0x429990);
  var _0xd50bec = {
    "fpdm": _0x56f0eb,
    "fphm": _0x43a40a,
    "r": _0x533c94,
    "v": 'V2.0.05_058',
    "nowtime": _0x429990,
    "area": _0x2649df,
    "publickey": _0x429990,
    "key9": $["nnyd"]["yzm"](_0x56f0eb, _0x43a40a, _0x429990)
  };
 // alert($["nnyd"]["yzm"](_0x56f0eb, _0x43a40a, _0x429990))
 // alert(_0x56bec6)
  $["ajaxSetup"]({
    "cache": false
  });
  yzmFlag = 1;
  var aa =  $["ajax"];
  $["ajax"]({
    "type": "get",
    "url": _0x56bec6,
    "data": _0xd50bec,
    "dataType": "jsonp",
    "jsonp": "callback",
    "success": function (_0x56adbf) {
      if (_0x56adbf["hasOwnProperty"]("data")) {
        _0x56adbf = _0x56adbf["data"];
        _0x56adbf = replaceStr(_0x56adbf, _0x429990);
        _0x56adbf = Base64["decode"](_0x56adbf);
        _0x56adbf = eval("(" + _0x56adbf + ")");
      }
//alert(_0x56bec6)
//alert(_0xd50bec)
      delayFlag = "1";
      var _0x22e76d = _0x56adbf["key1"];
      var _0x4e4450 = _0x56adbf["key2"];
      var _0x577e04 = _0x56adbf["key3"];
      var _0x1c1133 = _0x56adbf["key4"];
      var _0x57a134 = _0x56adbf["key5"];

      if (_0x57a134 == "2") {
        oldweb = 2;
      } else {
        if (_0x57a134 == "1") {
          oldweb = 1;
        }
      }

      if (_0x22e76d == "003") {
        jAlert("\u9A8C\u8BC1\u7801\u8BF7\u6C42\u6B21\u6570\u8FC7\u4E8E\u9891\u7E41\uFF0C\u8BF71\u5206\u949F\u540E\u518D\u8BD5\uFF01", "\u8B66\u544A");
        $("#yzm_img")["hide"]();
      } else {
        if (_0x22e76d == "005") {
          jAlert("\u975E\u6CD5\u8BF7\u6C42!", "\u8B66\u544A");
        } else {
          if (_0x22e76d == "010") {
            jAlert("\u7F51\u7EDC\u8D85\u65F6\uFF0C\u8BF7\u91CD\u8BD5\uFF01(01)", "\u8B66\u544A");
          } else {
            if (_0x22e76d == "fpdmerr") {
              jAlert("\u8BF7\u8F93\u5165\u5408\u6CD5\u53D1\u7968\u4EE3\u7801!", "\u8B66\u544A");
            } else {
              if (_0x22e76d == "024") {
                jAlert("24\u5C0F\u65F6\u5185\u9A8C\u8BC1\u7801\u8BF7\u6C42\u592A\u9891\u7E41\uFF0C\u8BF7\u7A0D\u540E\u518D\u8BD5\uFF01", "\u8B66\u544A");
                $("#yzm_img")["hide"]();
              } else {
                if (_0x22e76d == "016") {
                  jAlert("\u670D\u52A1\u5668\u63A5\u6536\u7684\u8BF7\u6C42\u592A\u9891\u7E41\uFF0C\u8BF7\u7A0D\u540E\u518D\u8BD5\uFF01", "\u8B66\u544A");
                  $("#yzm_img")["hide"]();
                } else {
                  if (_0x22e76d == "020") {
                    jAlert("\u7531\u4E8E\u67E5\u9A8C\u884C\u4E3A\u5F02\u5E38\uFF0C\u6D89\u5ACC\u8FDD\u89C4\uFF0C\u5F53\u524D\u65E0\u6CD5\u4F7F\u7528\u67E5\u9A8C\u670D\u52A1\uFF01", "\u63D0\u793A");
                  } else {
                    if (_0x22e76d == "errv") {
                      jAlert("\u5F53\u524D\u9875\u9762\u7248\u672C\u8F83\u4F4E\uFF0C\u8BF7\u6309CTRL-F5\u5237\u65B0\u9875\u9762\uFF01", "\u63D0\u793A");
                    } else {
                      if (_0x22e76d != "") {
                        $("#yzm_img")["attr"]("src", "data:image/png;base64," + _0x22e76d);
                        $("#yzm_unuse_img")["attr"]("src", "data:image/png;base64," + _0x22e76d);

                        if (_0x1c1133 == "00") {
                          $("#yzminfo")["text"]("\u8BF7\u8F93\u5165\u9A8C\u8BC1\u7801\u6587\u5B57");
                        } else {
                          if (_0x1c1133 == "01") {
                            $("#yzminfo")["html"]("\u8BF7\u8F93\u5165\u9A8C\u8BC1\u7801\u56FE\u7247\u4E2D<font color=\"red\" size=\"4\" style=\"background:#C0C0C0\">\u7EA2\u8272</font>\u6587\u5B57");
                          } else {
                            if (_0x1c1133 == "02") {
                              $("#yzminfo")["html"]("\u8BF7\u8F93\u5165\u9A8C\u8BC1\u7801\u56FE\u7247\u4E2D<font color=\"yellow\" size=\"4\" style=\"background:#C0C0C0\">\u9EC4\u8272</font>\u6587\u5B57");
                            } else {
                              if (_0x1c1133 == "03") {
                                $("#yzminfo")["html"]("\u8BF7\u8F93\u5165\u9A8C\u8BC1\u7801\u56FE\u7247\u4E2D<font color=\"blue\" size=\"4\" style=\"background:#C0C0C0\">\u84DD\u8272</font>\u6587\u5B57");
                              }
                            }
                          }
                        }

                        yzmSj = _0x4e4450;
                        jmmy = _0x577e04;
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    },
    "timeout": 5000,
    "error": function (_0x1cba09, _0x4ddbe5, _0x4be35e) {
      if (retrycount == 9) {
        jAlert("\u7CFB\u7EDF\u7E41\u5FD9\uFF0C\u8BF7\u7A0D\u540E\u91CD\u8BD5!", "\u63D0\u793A");
      } else {
        retrycount = retrycount + 1;
        getYzmXx();
      }
    }
  });
  yzmWait = 2;
  yzmTime($("#yzm_img"));
}
//getYzmXx()
function showTime() {
  var _0x5c212c = new Date();

  var _0x18eb72 = _0x5c212c["getTime"]();

  return _0x18eb72;
}