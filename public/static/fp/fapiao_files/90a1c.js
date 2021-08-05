var _0xade692 = function () {
  var _0x420008 = true;
  return function (_0x168e96, _0xad87c9) {
    var _0x18d9fc = _0x420008 ? function () {
      if (_0xad87c9) {
        var _0x2da316 = _0xad87c9["apply"](_0x168e96, arguments);

        _0xad87c9 = null;
        return _0x2da316;
      }
    } : function () {};

    _0x420008 = false;
    return _0x18d9fc;
  };
}();

var _0x56edba = _0xade692(this, function () {
  var _0x35436f = function () {
    return "dev";
  },
      _0x46eb6c = function () {
    return "window";
  };

  var _0x246ad1 = function () {
    var _0x104251 = new RegExp("\\w+ *\\(\\) *{\\w+ *['|\"].+['|\"];? *}");

    return !true;
  };

  var _0x2dea7a = function () {
    var _0x2c236a = new RegExp("(\\\\[x|u](\\w){2,4})+");

    return true;
  };

  var _0x58a15f = function (_0x4b78e3) {
    var _0x4e53aa = 0;

    if (_0x4b78e3["indexOf"](false)) {
      _0x303189(_0x4b78e3);
    }
  };

  var _0x303189 = function (_0x49e095) {
    var _0x20186b = 3;

    if (_0x49e095["indexOf"]("true"[3]) !== _0x20186b) {
      _0x58a15f(_0x49e095);
    }
  };

  if (!_0x246ad1()) {
    if (!_0x2dea7a()) {
      _0x58a15f("ind\u0435xOf");
    } else {
      _0x58a15f("indexOf");
    }
  } else {
    _0x58a15f("ind\u0435xOf");
  }
});

_0x56edba();

var _0xeb0961 = function () {
  var _0x4d8167 = true;
  return function (_0x417142, _0x2500d7) {
    var _0x4a4ff9 = _0x4d8167 ? function () {
      if (_0x2500d7) {
        var _0x319df1 = _0x2500d7["apply"](_0x417142, arguments);

        _0x2500d7 = null;
        return _0x319df1;
      }
    } : function () {};

    _0x4d8167 = false;
    return _0x4a4ff9;
  };
}();

var _0x347b52 = _0xeb0961(this, function () {
  var _0x215733 = function () {};

  var _0x1df9c7 = function () {
    var _0x23602a;

    try {
      _0x23602a = Function("return (function() {}.constructor(\"return this\")( ));")();
    } catch (_0x5a74cc) {
      _0x23602a = window;
    }

    return _0x23602a;
  };

  var _0x41b166 = _0x1df9c7();

  if (!_0x41b166["console"]) {
    _0x41b166["console"] = function (_0x16ab4a) {
      var _0x1c7f47 = {};
      _0x1c7f47["log"] = _0x16ab4a;
      _0x1c7f47["warn"] = _0x16ab4a;
      _0x1c7f47["debug"] = _0x16ab4a;
      _0x1c7f47["info"] = _0x16ab4a;
      _0x1c7f47["error"] = _0x16ab4a;
      _0x1c7f47["exception"] = _0x16ab4a;
      _0x1c7f47["trace"] = _0x16ab4a;
      return _0x1c7f47;
    }(_0x215733);
  } else {
    _0x41b166["console"]["log"] = _0x215733;
    _0x41b166["console"]["warn"] = _0x215733;
    _0x41b166["console"]["debug"] = _0x215733;
    _0x41b166["console"]["info"] = _0x215733;
    _0x41b166["console"]["error"] = _0x215733;
    _0x41b166["console"]["exception"] = _0x215733;
    _0x41b166["console"]["trace"] = _0x215733;
  }
});

_0x347b52();

$("#checkfp")["click"](function () {
  $("#checkfp")["hide"]();
  $("#uncheckfp")["show"]();

  var _0x11b1fc = $("#fpdm")["val"]()["trim"]();

  var _0x44a0e0 = $("#fphm")["val"]()["trim"]();

  var _0x28e069 = $("#kprq")["val"]()["trim"]();

  var _0x3e2a08 = getSwjg(_0x11b1fc, 1);

  var _0x4f7c33 = _0x3e2a08[2];

  if (_0x3e2a08["length"] > 0) {
    var _0x2fb2ba = /^[0-9]{4}[0-1]?[0-9]{1}[0-3]?[0-9]{1}$/;

    if (_0x2fb2ba["test"](_0x28e069)) {} else {
      jAlert("\u65E5\u671F\u683C\u5F0F\u9519\u8BEF\uFF0C\u4E3AYYYYMMDD\u683C\u5F0F\uFF01", "\u63D0\u793A");
      return;
    }

    var _0x3aa257 = $("#kjje")["val"]()["trim"]();

    if (aur()) {
      $("#checkfp")["hide"]();
      $("#uncheckfp")["show"]();

      var _0x15e300 = new Date();

      var _0x2074db = $("#yzm")["val"]()["trim"]();

      var _0x4f1dd0 = "";
      var _0x274fea = null;
      var _0x4f1965 = "";

      if (avai(fplx)) {
        if (fplx == "01" || fplx == "02" || fplx == "03" || fplx == "08") {
          var _0x2b110a = _0x3aa257["indexOf"](".");

          if (_0x2b110a > 0) {
            var _0xa08afa = _0x3aa257["split"](".");

            if (_0xa08afa[1] == "00" || _0xa08afa[1] == "0") {
              _0x3aa257 = _0xa08afa[0];
            } else {
              if (_0xa08afa[1]["charAt"](1) == "0") {
                _0x3aa257 = _0xa08afa[0] + "." + _0xa08afa[1]["charAt"](0);
              }
            }
          }
        }

        var _0x48d819 = _0x3e2a08[1];
        _0x4f1965 = _0x48d819 + "/vatQuery";
        _0x274fea = {
          "key1": _0x11b1fc,
          "key2": _0x44a0e0,
          "key3": _0x28e069,
          "key4": _0x3aa257,
          "fplx": fplx,
          "yzm": _0x2074db,
          "yzmSj": yzmSj,
          "index": jmmy,
          "publickey": yzmSj,
          "key9": $["nnyd"]["cy"](_0x11b1fc, _0x44a0e0, yzmSj)
        };

        if (oldweb == 1) {
          _0x4f1965 = _0x48d819 + "/invQuery";
          _0x274fea = {
            "fpdm": _0x11b1fc,
            "fphm": _0x44a0e0,
            "kprq": _0x28e069,
            "fpje": _0x3aa257,
            "fplx": fplx,
            "yzm": _0x2074db,
            "yzmSj": yzmSj,
            "index": jmmy,
            "publickey": yzmSj,
            "key9": $["nnyd"]["cy"](_0x11b1fc, _0x44a0e0, yzmSj)
          };
        }

        delayMessage = "\u53D1\u7968\u67E5\u9A8C\u8BF7\u6C42\u5931\u8D25!";
        showTime();
        $["ajax"]({
          "type": "GET",
          "url": _0x4f1965,
          "dataType": "jsonp",
          "data": _0x274fea,
          "jsonp": "callback",
          "success": function (_0x4b36e4) {
            if ((fplx == "08" || fplx == "01") && _0x4b36e4["hasOwnProperty"]("data")) {
              _0x4b36e4 = _0x4b36e4["data"];
              _0x4b36e4 = replaceStr(_0x4b36e4, yzmSj);
              _0x4b36e4 = Base64["decode"](_0x4b36e4);
              _0x4b36e4 = decodeURIComponent(_0x4b36e4, "UTF-8");
              _0x4b36e4 = eval("(" + _0x4b36e4 + ")");
            }

            delayFlag = "1";
            var _0x33aa28 = _0x4b36e4["key1"];
            var _0x49c248 = _0x4b36e4["key2"];

            if (_0x33aa28 == "1") {
              show_yzm = "";
              jAlert("\u8BE5\u7701\u5C1A\u672A\u5F00\u901A\u53D1\u7968\u67E5\u9A8C\u529F\u80FD\uFF01", "\u63D0\u793A");
            } else {
              if (_0x33aa28 == "001") {
                show_yzm = "";
                var _0x4ec5a1 = _0x4b36e4["key5"];
                var _0x5e2503 = _0x4b36e4["key3"];
                var _0x5af3a1 = _0x4b36e4["key2"];

                var _0x2441c = _0x4b36e4["key4"]["trim"]();

                if (_0x4ec5a1 != "1") {
                  var _0x4876be = $["vsign"](fplx, _0x11b1fc, _0x4b36e4["key2"], _0x4ec5a1);

                  if (_0x4876be) {
                    _0x5af3a1 = $["endetail"](fplx, _0x11b1fc, $["deinv"](fplx, _0x11b1fc, _0x4b36e4["key2"]));
                    _0x5e2503 = $["deinvkey"](fplx, _0x11b1fc, _0x4b36e4["key3"]);
                    _0x2441c = $["deinvrm"](fplx, _0x11b1fc, _0x2441c);
                  } else {
                    jAlert("\u7CFB\u7EDF\u5F02\u5E38\uFF0C\u8BF7\u91CD\u8BD5\uFF01(07)", "\u7CFB\u7EDF\u9519\u8BEF");
                    return;
                  }
                }

                if (browser == "edge" || browser == "firefox") {
                  show_dialog(1100, 700, "cyjgedge" + fplx + ".html", _0x11b1fc + "\u2261" + _0x44a0e0 + "\u2261" + swjgmc + "\u2261" + _0x5af3a1 + "\u2261" + yzmSj + "\u2261\u2261\u2261HWXX\u2261\u2261\u2261" + _0x5e2503 + "\u2261\u2261\u2261HWXX\u2261\u2261\u2261" + _0x2441c);
                } else {
                  if (browser == "ie8") {
                    sessionStorage["browser"] = "ie8";

                    var _0x43bd2c = JSON["stringify"](_0x11b1fc + "\u2261" + _0x44a0e0 + "\u2261" + swjgmc + "\u2261" + _0x5af3a1 + "\u2261" + yzmSj + "\u2261\u2261\u2261HWXX\u2261\u2261\u2261" + _0x5e2503 + "\u2261\u2261\u2261HWXX\u2261\u2261\u2261" + _0x2441c);

                    sessionStorage["result"] = _0x43bd2c;
                    show_dialog(1100, 700, "cyjgedge" + fplx + ".html", _0x11b1fc + "\u2261" + _0x44a0e0 + "\u2261" + swjgmc + "\u2261" + _0x5af3a1 + "\u2261" + yzmSj + "\u2261\u2261\u2261HWXX\u2261\u2261\u2261" + _0x5e2503 + "\u2261\u2261\u2261HWXX\u2261\u2261\u2261" + _0x2441c);
                  } else {
                    window["showModalDialog"]("cyjg" + fplx + ".html", _0x11b1fc + "\u2261" + _0x44a0e0 + "\u2261" + swjgmc + "\u2261" + _0x5af3a1 + "\u2261" + yzmSj + "\u2261\u2261\u2261HWXX\u2261\u2261\u2261" + _0x5e2503 + "\u2261\u2261\u2261HWXX\u2261\u2261\u2261" + _0x2441c, "dialogTop:10px;dialogWidth:1100px;dialogHeight:700px;");
                  }
                }
              } else {
                if (_0x33aa28 == "002") {
                  show_yzm = "";
                  jAlert("\u8D85\u8FC7\u8BE5\u5F20\u53D1\u7968\u5F53\u65E5\u67E5\u9A8C\u6B21\u6570(\u8BF7\u4E8E\u6B21\u65E5\u518D\u6B21\u67E5\u9A8C)!", "\u63D0\u793A");
                } else {
                  if (_0x33aa28 == "003") {
                    show_yzm = "";
                    jAlert("\u53D1\u7968\u67E5\u9A8C\u8BF7\u6C42\u592A\u9891\u7E41\uFF0C\u8BF7\u7A0D\u540E\u518D\u8BD5\uFF01", "\u63D0\u793A");
                  } else {
                    if (_0x33aa28 == "004") {
                      show_yzm = "";
                      jAlert("\u8D85\u8FC7\u670D\u52A1\u5668\u6700\u5927\u8BF7\u6C42\u6570\uFF0C\u8BF7\u7A0D\u540E\u8BBF\u95EE!", "\u63D0\u793A");
                    } else {
                      if (_0x33aa28 == "005") {
                        show_yzm = "";
                        jAlert("\u8BF7\u6C42\u4E0D\u5408\u6CD5!", "\u63D0\u793A");
                      } else {
                        if (_0x33aa28 == "020") {
                          show_yzm = "";
                          jAlert("\u7531\u4E8E\u67E5\u9A8C\u884C\u4E3A\u5F02\u5E38\uFF0C\u6D89\u5ACC\u8FDD\u89C4\uFF0C\u5F53\u524D\u65E0\u6CD5\u4F7F\u7528\u67E5\u9A8C\u670D\u52A1\uFF01", "\u63D0\u793A");
                        } else {
                          if (_0x33aa28 == "006") {
                            var _0x292b34 = _0x4b36e4["key2"];
                            var _0x19cdbd = _0x4b36e4["key3"];
                            show_yzm = "";
                            _0x4f1dd0 = "\u4E0D\u4E00\u81F4";
                            _0x274fea = {
                              "fplx": fplx,
                              "swjg": swjgmc,
                              "fpdm": _0x11b1fc,
                              "fphm": _0x44a0e0,
                              "kprq": _0x28e069,
                              "kjje": _0x3aa257,
                              "cysj": yzmSj,
                              "setText": _0x4f1dd0,
                              "key2222": _0x292b34,
                              "key3333": _0x19cdbd
                            };

                            if (browser == "edge" || browser == "firefox") {
                              show_dialog(800, 400, "jgbyzedge.html", _0x274fea);
                            } else {
                              window["showModalDialog"]("jgbyz.html", _0x274fea, "dialogWidth:800px;dialogHeight:400px;center:yes;scroll:no");
                            }
                          } else {
                            if (_0x33aa28 == "007") {
                              show_yzm = "";
                              jAlert("\u9A8C\u8BC1\u7801\u5931\u6548!", "\u63D0\u793A");
                            } else {
                              if (_0x33aa28 == "008") {
                                show_yzm = "";
                                jAlert("\u9A8C\u8BC1\u7801\u9519\u8BEF!", "\u63D0\u793A");
                              } else {
                                if (_0x33aa28 == "009" || _0x33aa28 == "010" && _0x49c248 == "(06)") {
                                  var _0x292b34 = _0x4b36e4["key2"];
                                  var _0x19cdbd = _0x4b36e4["key3"];
                                  show_yzm = "";
                                  _0x4f1dd0 = "\u67E5\u65E0\u6B64\u7968";
                                  _0x274fea = {
                                    "fplx": fplx,
                                    "swjg": swjgmc,
                                    "fpdm": _0x11b1fc,
                                    "fphm": _0x44a0e0,
                                    "kprq": _0x28e069,
                                    "kjje": _0x3aa257,
                                    "cysj": yzmSj,
                                    "setText": _0x4f1dd0,
                                    "key2222": _0x292b34,
                                    "key3333": _0x19cdbd
                                  };

                                  if (browser == "edge" || browser == "firefox") {
                                    show_dialog(800, 400, "jgbyzedge.html", _0x274fea);
                                  } else {
                                    window["showModalDialog"]("jgbyz.html", _0x274fea, "dialogWidth:800px;dialogHeight:400px;center:yes;scroll:no");
                                  }
                                } else {
                                  if (_0x33aa28 == "rqerr") {
                                    show_yzm = "";
                                    jAlert("\u5F53\u65E5\u5F00\u5177\u53D1\u7968\u53EF\u4E8E\u6B21\u65E5\u8FDB\u884C\u67E5\u9A8C\uFF01", "\u8B66\u544A");
                                  } else {
                                    if (_0x33aa28 == "010") {
                                      show_yzm = "";
                                      var _0xfdc103 = _0x4b36e4["key2"];

                                      if (_0xfdc103 == "inredis") {
                                        _0xfdc103 = "(02)";
                                      } else {
                                        if (_0xfdc103 == "weberr") {
                                          _0xfdc103 = "(03)";
                                        } else {
                                          if (_0xfdc103 == "inoracle") {
                                            _0xfdc103 = "(08)";
                                          } else {
                                            if (_0xfdc103 == "indata") {
                                              _0xfdc103 = "(09)";
                                            } else {
                                              if (_0xfdc103 == "incjoracle") {
                                                _0xfdc103 = "(10)";
                                              }
                                            }
                                          }
                                        }
                                      }

                                      jAlert("\u7CFB\u7EDF\u5F02\u5E38\uFF0C\u8BF7\u91CD\u8BD5\uFF01" + _0xfdc103, "\u7CFB\u7EDF\u9519\u8BEF");
                                    } else {
                                      if (_0x33aa28 == "010_") {
                                        show_yzm = "";
                                        jAlert("\u7CFB\u7EDF\u5F02\u5E38\uFF0C\u8BF7\u91CD\u8BD5\uFF01(05)", "\u7CFB\u7EDF\u9519\u8BEF");
                                      } else {
                                        if (_0x33aa28 == "015") {
                                          show_yzm = "";
                                          jAlert("\u7CFB\u7EDF\u5F02\u5E38\uFF0C\u8BF7\u91CD\u8BD5\uFF01(015)", "\u7CFB\u7EDF\u9519\u8BEF");
                                        } else {
                                          if (key1 == "016") {
                                            show_yzm = "";
                                            jAlert("\u670D\u52A1\u5668\u63A5\u6536\u7684\u8BF7\u6C42\u592A\u9891\u7E41\uFF0C\u8BF7\u7A0D\u540E\u518D\u8BD5\uFF01", "\u8B66\u544A");
                                          } else {
                                            show_yzm = "";
                                            jAlert("\u7CFB\u7EDF\u5F02\u5E38\uFF0C\u8BF7\u91CD\u8BD5\uFF01(04)", "\u7CFB\u7EDF\u9519\u8BEF");
                                          }
                                        }
                                      }
                                    }
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }

            window["parent"]["$"]["pricode"]["clearB"]();
            $["pricode"]["clearB"]();
            $("#uncheckfp")["hide"]();
            $("#checkfp")["show"]();
          },
          "timeout": 30000,
          "error": function (_0x5dedd1, _0x3dc148, _0x3286e) {
            if (_0x3dc148 === "timeout") {
              jAlert("\u7CFB\u7EDF\u5F02\u5E38\uFF0C\u8BF7\u6C42\u8D85\u65F6\uFF01", "\u7CFB\u7EDF\u9519\u8BEF");
            }
          }
        });
      }
    }
  }
});