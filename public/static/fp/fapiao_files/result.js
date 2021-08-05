var _0x3a29a3 = function () {
  var _0x682c18 = true;
  return function (_0x88c955, _0x289d4a) {
    var _0x2327a8 = _0x682c18 ? function () {
      if (_0x289d4a) {
        var _0x4ba46b = _0x289d4a["apply"](_0x88c955, arguments);

        _0x289d4a = null;
        return _0x4ba46b;
      }
    } : function () {};

    _0x682c18 = false;
    return _0x2327a8;
  };
}();

var _0x234f92 = _0x3a29a3(this, function () {
  var _0x25c41c = function () {
    return "dev";
  },
      _0xc96bde = function () {
    return "window";
  };

  var _0x50c01a = function () {
    var _0x5be8d3 = new RegExp("\\w+ *\\(\\) *{\\w+ *['|\"].+['|\"];? *}");

    return !true;
  };

  var _0x2458e6 = function () {
    var _0x4c1229 = new RegExp("(\\\\[x|u](\\w){2,4})+");

    return true;
  };

  var _0x1615fb = function (_0x325840) {
    var _0x4556a9 = 0;

    if (_0x325840["indexOf"](false)) {
      _0x93922(_0x325840);
    }
  };

  var _0x93922 = function (_0x5ca1b9) {
    var _0x6107c2 = 3;

    if (_0x5ca1b9["indexOf"]("true"[3]) !== _0x6107c2) {
      _0x1615fb(_0x5ca1b9);
    }
  };

  if (!_0x50c01a()) {
    if (!_0x2458e6()) {
      _0x1615fb("ind\u0435xOf");
    } else {
      _0x1615fb("indexOf");
    }
  } else {
    _0x1615fb("ind\u0435xOf");
  }
});

_0x234f92();

var _0x69a91e = function () {
  var _0x11a983 = true;
  return function (_0x21137f, _0x2e9e58) {
    var _0x3c3e20 = _0x11a983 ? function () {
      if (_0x2e9e58) {
        var _0x348d7e = _0x2e9e58["apply"](_0x21137f, arguments);

        _0x2e9e58 = null;
        return _0x348d7e;
      }
    } : function () {};

    _0x11a983 = false;
    return _0x3c3e20;
  };
}();

var _0x1c5700 = _0x69a91e(this, function () {
  var _0x117744 = function () {};

  var _0x416ba8;

  try {
    var _0x284d7e = Function("return (function() {}.constructor(\"return this\")( ));");

    _0x416ba8 = _0x284d7e();
  } catch (_0xe1a827) {
    _0x416ba8 = window;
  }

  if (!_0x416ba8["console"]) {
    _0x416ba8["console"] = function (_0x251c9f) {
      var _0x1e9d62 = {};
      _0x1e9d62["log"] = _0x251c9f;
      _0x1e9d62["warn"] = _0x251c9f;
      _0x1e9d62["debug"] = _0x251c9f;
      _0x1e9d62["info"] = _0x251c9f;
      _0x1e9d62["error"] = _0x251c9f;
      _0x1e9d62["exception"] = _0x251c9f;
      _0x1e9d62["trace"] = _0x251c9f;
      return _0x1e9d62;
    }(_0x117744);
  } else {
    _0x416ba8["console"]["log"] = _0x117744;
    _0x416ba8["console"]["warn"] = _0x117744;
    _0x416ba8["console"]["debug"] = _0x117744;
    _0x416ba8["console"]["info"] = _0x117744;
    _0x416ba8["console"]["error"] = _0x117744;
    _0x416ba8["console"]["exception"] = _0x117744;
    _0x416ba8["console"]["trace"] = _0x117744;
  }
});

_0x1c5700();

function oldGetjsfpHwxxHtml(_0x4a639f, _0x46bb6f, _0x1e3e9b) {
  var _0x4bd89f = _0x4a639f["split"]("\u2261");

  var _0x4b56e0;

  var _0xb9ec6d = "";

  for (var _0x2d7bae = 0; _0x2d7bae < _0x4bd89f["length"]; _0x2d7bae++) {
    _0x4b56e0 = _0x4bd89f[_0x2d7bae]["split"]("\u2588");
    _0xb9ec6d += "<tr>";
    var _0x3d1989 = "";

    for (var _0x130d19 = 0; _0x130d19 < 4; _0x130d19++) {
      if (_0x130d19 == 1) {
        _0x3d1989 = "<td class=\"align_right\"><span class=\"content_td_blue\">" + _0x4b56e0[_0x130d19] + "</span></td>";
        continue;
      }

      if (_0x130d19 == 2 || _0x130d19 == 3) {
        _0xb9ec6d += "<td class=\"align_right\"><span class=\"content_td_blue\">";
      } else {
        _0xb9ec6d += "<td class=\"align_center\"><span class=\"content_td_blue\">";
      }

      if (_0x130d19 == 0) {
        _0xb9ec6d += oldFormatHwmc(_0x4b56e0[_0x130d19], _0x46bb6f);
      } else {
        if (_0x130d19 == 1) {
          _0xb9ec6d += getzeroDot(_0x4b56e0[_0x130d19]);
        } else {
          if (_0x130d19 == 2 || _0x130d19 == 3) {
            if (_0x130d19 == 2) {
              _0xb9ec6d += getzeroDot(GetJeToDot(_0x4b56e0[_0x130d19]));
              _0xb9ec6d += _0x3d1989;
              _0x3d1989 = "";
            } else {
              _0xb9ec6d += getzeroDot(GetJeToDot(_0x4b56e0[_0x130d19]));
            }
          } else {
            _0xb9ec6d += _0x4b56e0[_0x130d19];
          }
        }
      }

      _0xb9ec6d += "</span></td>";
    }

    _0xb9ec6d += "</tr>";
  }

  return _0xb9ec6d;
}

function oldGethyzpHwxxHtml(_0x2d9ae9, _0x366de8, _0x5a0109, _0x46f399) {
  var _0x592378 = _0x2d9ae9["split"]("\u2261");

  var _0x54b314;

  var _0x4ecc4e = "";

  for (var _0x17635c = _0x366de8; _0x17635c < _0x592378["length"]; _0x17635c = _0x17635c + 2) {
    _0x54b314 = _0x592378[_0x17635c]["split"]("\u2588");
    _0x4ecc4e += "<tr>";

    for (var _0x565081 = 0; _0x565081 < 2; _0x565081++) {
      if (_0x565081 == 1) {
        _0x4ecc4e += "<td class=\"align_right\"><span class=\"content_td_blue\">";
      } else {
        _0x4ecc4e += "<td class=\"align_center\"><span class=\"content_td_blue\">";
      }

      if (_0x565081 == 0) {
        _0x4ecc4e += oldFormatHwmc(_0x54b314[_0x565081], _0x5a0109);
      } else {
        if (_0x565081 == 1) {
          _0x4ecc4e += getzeroDot(GetJeToDot(_0x54b314[_0x565081], _0x46f399));
        }
      }

      _0x4ecc4e += "</span></td>";
    }

    _0x4ecc4e += "<td >&nbsp;</td></tr>";
  }

  return _0x4ecc4e;
}

function oldGetDzHwxxHtml(_0x388305, _0x589471, _0x173410) {
  var _0x14bf8d = _0x388305["split"]("\u2261");

  var _0x1885a6;

  var _0x2c8a4d = "";

  for (var _0x40460e = 0; _0x40460e < _0x14bf8d["length"]; _0x40460e++) {
    _0x1885a6 = _0x14bf8d[_0x40460e]["split"]("\u2588");
    _0x2c8a4d += "<tr>";

    for (var _0x3e95f2 = 0; _0x3e95f2 < 8; _0x3e95f2++) {
      if (_0x3e95f2 != 7) {
        if (_0x3e95f2 == 3 || _0x3e95f2 == 4 || _0x3e95f2 == 5 || _0x3e95f2 == 6) {
          _0x2c8a4d += "<td class=\"align_right borderRight\"><span class=\"content_td_blue\">";
        } else {
          _0x2c8a4d += "<td class=\"align_left borderRight\"><span class=\"content_td_blue\">";
        }
      } else {
        _0x2c8a4d += "<td class=\"align_right\"><span class=\"content_td_blue\">";
      }

      if (_0x3e95f2 == 3) {
        _0x2c8a4d += getzeroDot(_0x1885a6[6]);
      } else {
        if (_0x3e95f2 == 4 || _0x3e95f2 == 5 || _0x3e95f2 == 7) {
          _0x2c8a4d += getzeroDot(GetJeToDot(_0x1885a6[_0x3e95f2]["trim"]()));
        } else {
          if (_0x3e95f2 == 0) {
            _0x2c8a4d += oldFormatHwmc(_0x1885a6[_0x3e95f2], _0x589471);
          } else {
            if (_0x3e95f2 == 6) {
              _0x2c8a4d += FormatSl(_0x1885a6[3]);
            } else {
              _0x2c8a4d += _0x1885a6[_0x3e95f2];
            }
          }
        }
      }

      _0x2c8a4d += "</span></td>";
    }

    _0x2c8a4d += "</tr>";
  }

  return _0x2c8a4d;
}

function oldshowmx(_0x327861, _0x5ab16c, _0x3a88c1) {
  var _0x1c1d31 = sechw["split"]("\u258E");

  var _0x4b6993;

  var _0x9f13f6 = "";

  if (_0x1c1d31[0] != "") {
    for (var _0x25d0a3 = 0; _0x25d0a3 < _0x1c1d31["length"]; _0x25d0a3++) {
      _0x4b6993 = _0x1c1d31[_0x25d0a3]["split"]("\u2588");
      _0x9f13f6 += "<tr><td class=\"borderBottomTopNo content_td_blue\">" + (_0x25d0a3 + 1) + "</td>";

      for (var _0x51dd56 = 0; _0x51dd56 < 8; _0x51dd56++) {
        if (_0x51dd56 == 0) {
          _0x9f13f6 += "<td class=\"borderBottomTopNo align_left content_td_blue\">";
        } else {
          if (_0x51dd56 == 3 || _0x51dd56 == 4 || _0x51dd56 == 5 || _0x51dd56 == 6 || _0x51dd56 == 7) {
            _0x9f13f6 += "<td class=\"borderBottomTopNo align_right content_td_blue\">";
          } else {
            _0x9f13f6 += "<td class=\"borderBottomTopNo align_left content_td_blue\">";
          }
        }

        if (_0x51dd56 == 6) {
          _0x9f13f6 += FormatSl(_0x4b6993[_0x51dd56]);
        } else {
          if (_0x51dd56 == 3) {
            _0x9f13f6 += getzeroDot(_0x4b6993[_0x51dd56]);
          } else {
            if (_0x51dd56 == 4 || _0x51dd56 == 5 || _0x51dd56 == 7) {
              _0x9f13f6 += getzeroDot(GetJeToDot(_0x4b6993[_0x51dd56], _0x5ab16c));
            } else {
              if (_0x51dd56 == 0) {
                _0x9f13f6 += oldFormatHwmc(_0x4b6993[_0x51dd56], _0x327861);
              } else {
                _0x9f13f6 += _0x4b6993[_0x51dd56];
              }
            }
          }
        }

        _0x9f13f6 += "</td>";
      }

      _0x9f13f6 += "</tr>";
    }
  }

  $("#tab_head_mx")["after"](_0x9f13f6);
  sechw = "";

  if (_0x3a88c1 == "01" || _0x3a88c1 == "08") {
    $("#xiaoji1")["text"]($("#je_zp")["text"]());
    $("#xiaoji2")["text"]($("#se_zp")["text"]());
    $("#zongji1")["text"]($("#je_zp")["text"]());
    $("#zongji2")["text"]($("#se_zp")["text"]());
  } else {
    if (_0x3a88c1 == "04") {
      $("#xiaoji1")["text"]($("#je_pp")["text"]());
      $("#xiaoji2")["text"]($("#se_pp")["text"]());
      $("#zongji1")["text"]($("#je_pp")["text"]());
      $("#zongji2")["text"]($("#se_pp")["text"]());
    }
  }

  popWin("hwmxqd");
  return;
}

function oldgetje(_0x384654, _0x543135) {
  if (typeof _0x384654 != "undefined" && _0x384654 != "") {
    return oldaccAdd(_0x384654, _0x543135);
  } else {
    return _0x384654;
  }
}

function oldaccAdd(_0x5a1dd6, _0x9d957d) {
  var _0x342ca6, _0x5c190e, _0x153cc4;

  if (_0x5a1dd6["trim"]() == "") {
    return _0x5a1dd6;
  }

  if (parseInt(_0x5a1dd6, 10) == _0x5a1dd6) {
    _0x342ca6 = 0;
  } else {
    _0x342ca6 = _0x5a1dd6["toString"]()["split"](".")[1]["length"];
  }

  if (parseInt(_0x9d957d, 10) == _0x9d957d) {
    _0x5c190e = 0;
  } else {
    _0x5c190e = _0x9d957d["toString"]()["split"](".")[1]["length"];
  }

  _0x153cc4 = Math["pow"](10, Math["max"](_0x342ca6, _0x5c190e));

  var _0xceaca7 = (_0x5a1dd6 * _0x153cc4 + _0x9d957d * _0x153cc4) / _0x153cc4;

  return _0xceaca7["toFixed"](2);
}

function oldFormatHwmc(_0x2b56a9, _0x3ec1bb) {
  var _0x3efcc6 = _0x2b56a9["replaceAll"](_0x3ec1bb, "");

  return _0x3efcc6;
}

function oldFormatDate(_0x3d3e7d, _0x101905) {
  var _0x1ea4a9 = _0x3d3e7d["substring"](0, 4);

  var _0x51dbca = parseInt(_0x3d3e7d["substring"](4, 6), 10);

  var _0xfc51fd = parseInt(_0x3d3e7d["substring"](6), 10);

  var _0x4250b2 = new Date(_0x1ea4a9 + "/" + _0x51dbca + "/" + _0xfc51fd);

  _0x4250b2["setDate"](_0x4250b2["getDate"]() + (0 - _0x101905));

  var _0x1597fd = _0x4250b2["getFullYear"]() + "\u5E74" + (_0x4250b2["getMonth"]() + 1 > 9 ? _0x4250b2["getMonth"]() + 1 : "0" + (_0x4250b2["getMonth"]() + 1)) + "\u6708" + (_0x4250b2["getDate"]() > 9 ? _0x4250b2["getDate"]() : "0" + _0x4250b2["getDate"]()) + "\u65E5";

  return _0x1597fd;
}

function oldFormatSBH(_0x48c516, _0x5d35e2) {
  var _0x2f82bb = _0x5d35e2["split"]("_");

  for (var _0x2e2485 = 0; _0x2e2485 < _0x2f82bb["length"]; _0x2e2485++) {
    _0x48c516 = oldchgchar(_0x48c516, _0x2f82bb[_0x2e2485]);
  }

  return _0x48c516;
}

function oldchgchar(_0x4199a3, _0x15cb4a) {
  var _0x377399 = _0x15cb4a["charAt"](2);

  var _0x5c0b1a = _0x15cb4a["charAt"](0);

  _0x4199a3 = _0x4199a3["replaceAll"](_0x377399, "#");
  _0x4199a3 = _0x4199a3["replaceAll"](_0x5c0b1a, "%");
  _0x4199a3 = _0x4199a3["replaceAll"]("#", _0x5c0b1a);
  _0x4199a3 = _0x4199a3["replaceAll"]("%", _0x377399);
  return _0x4199a3;
}

function oldGetHwxxHtml(_0x2fe017, _0x31ff5c, _0x1d2bd7, _0x4d40cd) {
  var _0x5c40c2 = _0x2fe017["split"]("\u2584");

  if (_0x5c40c2["length"] > 1) {
    _0x2fe017 = _0x5c40c2[0];
  }

  var _0x458b69 = _0x2fe017["split"]("\u2261");

  var _0x2ffb80;

  var _0x1b5045 = "";
  var _0x380e27 = "";

  for (var _0x19e364 = 0; _0x19e364 < _0x458b69["length"]; _0x19e364++) {
    _0x2ffb80 = _0x458b69[_0x19e364]["split"]("\u2588");
    _0x1b5045 += "<tr>";

    for (var _0x38501f = 0; _0x38501f < 8; _0x38501f++) {
      if (_0x38501f != 7) {
        if (_0x38501f == 3 || _0x38501f == 4 || _0x38501f == 5 || _0x38501f == 6) {
          _0x1b5045 += "<td class=\"align_right borderRight\"><span class=\"content_td_blue\">";
        } else {
          _0x1b5045 += "<td class=\"align_left borderRight\"><span class=\"content_td_blue\">";
        }
      } else {
        _0x1b5045 += "<td class=\"align_right\"><span class=\"content_td_blue\">";
      }

      if (_0x38501f == 6) {
        if (_0x4d40cd == 14) {
          if (_0x2ffb80[8] == "1") {
            _0x1b5045 += "\u514D\u7A0E";
          } else {
            if (_0x2ffb80[8] == "2") {
              _0x1b5045 += "\u4E0D\u5F81\u7A0E";
            } else {
              _0x1b5045 += FormatSl(_0x2ffb80[_0x38501f]);
            }
          }
        } else {
          _0x1b5045 += FormatSl(_0x2ffb80[_0x38501f]);
        }
      } else {
        if (_0x38501f == 7 && _0x4d40cd == 14) {
          if (_0x2ffb80[8] == "1" || _0x2ffb80[8] == "2" || _0x2ffb80[8] == "3") {
            _0x1b5045 += "***";
          } else {
            _0x1b5045 += getzeroDot(GetJeToDot(_0x2ffb80[_0x38501f]["trim"]()));
          }
        } else {
          if (_0x38501f == 4 || _0x38501f == 5 || _0x38501f == 7) {
            if (_0x38501f == 4 && _0x4d40cd == 14) {
              _0x1b5045 += _0x2ffb80[_0x38501f];
            } else {
              _0x1b5045 += getzeroDot(GetJeToDot(_0x2ffb80[_0x38501f]["trim"]()));
            }
          } else {
            if (_0x38501f == 3) {
              _0x1b5045 += getzeroDot(_0x2ffb80[_0x38501f]);
            } else {
              if (_0x38501f == 0) {
                _0x380e27 = oldFormatHwmc(_0x2ffb80[_0x38501f], _0x31ff5c);
                _0x1b5045 += _0x380e27;
              } else {
                _0x1b5045 += _0x2ffb80[_0x38501f];
              }
            }
          }
        }
      }

      _0x1b5045 += "</span></td>";
    }

    _0x1b5045 += "</tr>";
  }

  if (_0x5c40c2["length"] > 1) {
    sechw = _0x5c40c2[1];
    _0x1b5045 += "<tr>";
    _0x1b5045 += "<td class=\"align_center borderRight\"><span class=\"content_td_blue\"><button id=\"showmx\" class=\"blue_button\" style=\"position:relative!important;z-index:100\" onmousemove=\"this.className='green_button';\" onmouseout=\"this.className='blue_button';\" onclick=\"oldshowmx('" + _0x31ff5c + "','" + _0x1d2bd7 + "','" + _0x4d40cd + "');\">\u67E5\u770B\u8D27\u7269\u660E\u7EC6\u6E05\u5355</button>";
    _0x1b5045 += "</span></td>";

    for (var _0x38501f = 0; _0x38501f < 7; _0x38501f++) {
      if (_0x38501f == 6) {
        _0x1b5045 += "<td class=\"align_center\"><span class=\"content_td_blue\">&nbsp;</span></td>";
      } else {
        _0x1b5045 += "<td class=\"align_center borderRight\"><span class=\"content_td_blue\">&nbsp;</span></td>";
      }
    }

    _0x1b5045 += "</tr>";
  }

  return _0x1b5045;
}

String["prototype"]["replaceAll"] = function (_0xa8767e, _0x599938, _0x53d354) {
  if (!RegExp["prototype"]["isPrototypeOf"](_0xa8767e)) {
    return this["replace"](new RegExp(_0xa8767e, _0x53d354 ? "gi" : "g"), _0x599938);
  } else {
    return this["replace"](_0xa8767e, _0x599938);
  }
};

function FormatDate(_0x2fe362) {
  var _0x5d04b1 = _0x2fe362["substring"](0, 4);

  var _0x2aa503 = parseInt(_0x2fe362["substring"](4, 6), 10);

  var _0x33ffed = parseInt(_0x2fe362["substring"](6), 10);

  var _0x235d8b = new Date(_0x5d04b1 + "/" + _0x2aa503 + "/" + _0x33ffed);

  var _0x47012d = _0x235d8b["getFullYear"]() + "\u5E74" + (_0x235d8b["getMonth"]() + 1 > 9 ? _0x235d8b["getMonth"]() + 1 : "0" + (_0x235d8b["getMonth"]() + 1)) + "\u6708" + (_0x235d8b["getDate"]() > 9 ? _0x235d8b["getDate"]() : "0" + _0x235d8b["getDate"]()) + "\u65E5";

  return _0x47012d;
}

function NoToChinese(_0x461272, _0x4c7ab0) {
  var _0x1380b0 = 99999999999.99;
  var _0x53e955 = "\u96F6";
  var _0x2883be = "\u58F9";
  var _0x2b2318 = "\u8D30";
  var _0x4d4422 = "\u53C1";
  var _0xc76820 = "\u8086";
  var _0x3421cb = "\u4F0D";
  var _0x1d37cd = "\u9646";
  var _0x146973 = "\u67D2";
  var _0x210f87 = "\u634C";
  var _0x357877 = "\u7396";
  var _0x4fc4d9 = "\u62FE";
  var _0x24c21b = "\u4F70";
  var _0x2d2592 = "\u4EDF";
  var _0x17e132 = "\u4E07";
  var _0x21ec83 = "\u4EBF";
  var _0x3b5477 = "";
  var _0xbe74d5 = "\u5706";
  var _0x1f38c8 = "\u89D2";
  var _0xbfc9bc = "\u5206";
  var _0x12d3cc = "\u6574";

  if (_0x4c7ab0 == "02" || _0x4c7ab0 == "03") {
    _0xbe74d5 = "\u5143";
  }

  var _0x35d32e;

  var _0x428104;

  var _0x3756d9;

  var _0xc056d0;

  var _0x161f42, _0x2b0a2b, _0x2429bf, _0x2ed94d;

  var _0x34a145;

  var _0x3b7d17, _0x57a3af, _0x376819;

  var _0x51a6ad, _0x5a4506;

  _0x461272 = _0x461272["toString"]();

  if (_0x461272["trim"]() == "") {
    return "";
  }

  if (_0x461272["match"](/[^,.\d]/) != null) {
    if (_0x461272["substring"](0, 1) != "-") {
      alert("\u5C0F\u5199\u91D1\u989D\u542B\u6709\u65E0\u6548\u5B57\u7B26\uFF01");
      return "";
    }
  }

  if (_0x461272["match"](/^((\d{1,3}(,\d{3})*(.((\d{3},)*\d{1,3}))?)|(\d+(.\d+)?))$/) == null) {
    if (_0x461272["substring"](0, 1) != "-") {
      alert("\u5C0F\u5199\u91D1\u989D\u7684\u683C\u5F0F\u4E0D\u6B63\u786E\uFF01");
      return "";
    }
  }

  var _0x231039 = "";

  if (_0x461272["substring"](0, 1) == "-") {
    if (_0x4c7ab0 == "01" || _0x4c7ab0 == "04" || _0x4c7ab0 == "08") {
      _0x231039 = "\uFF08\u8D1F\u6570\uFF09";
    } else {
      if (_0x4c7ab0 == "02" || _0x4c7ab0 == "03" || _0x4c7ab0 == "11") {
        _0x231039 = "\u8D1F\u6570\uFF1A";
      } else {
        if (_0x4c7ab0 == "10") {
          _0x231039 = "\u8D1F";
        } else {
          _0x231039 = "\uFF08\u8D1F\u6570\uFF09";
        }
      }
    }

    _0x461272 = _0x461272["substring"](1, _0x461272["length"]);
  }

  _0x461272 = _0x461272["replace"](/,/g, "");
  _0x461272 = _0x461272["replace"](/^0+/, "");

  if (Number(_0x461272) > _0x1380b0) {
    alert("\u91D1\u989D\u8FC7\u5927\uFF0C\u5E94\u5C0F\u4E8E1000\u4EBF\u5143\uFF01");
    return "";
  }

  _0xc056d0 = _0x461272["split"](".");

  if (_0xc056d0["length"] > 1) {
    _0x35d32e = _0xc056d0[0];
    _0x428104 = _0xc056d0[1];
    _0x428104 = _0x428104["substr"](0, 2);
  } else {
    _0x35d32e = _0xc056d0[0];
    _0x428104 = "";
  }

  _0x161f42 = new Array(_0x53e955, _0x2883be, _0x2b2318, _0x4d4422, _0xc76820, _0x3421cb, _0x1d37cd, _0x146973, _0x210f87, _0x357877);
  _0x2b0a2b = new Array("", _0x4fc4d9, _0x24c21b, _0x2d2592);
  _0x2429bf = new Array("", _0x17e132, _0x21ec83);
  _0x2ed94d = new Array(_0x1f38c8, _0xbfc9bc);
  _0x3756d9 = "";

  if (Number(_0x35d32e) > 0) {
    _0x34a145 = 0;

    for (_0x3b7d17 = 0; _0x3b7d17 < _0x35d32e["length"]; _0x3b7d17++) {
      _0x57a3af = _0x35d32e["length"] - _0x3b7d17 - 1;
      _0x376819 = _0x35d32e["substr"](_0x3b7d17, 1);
      _0x51a6ad = _0x57a3af / 4;
      _0x5a4506 = _0x57a3af % 4;

      if (_0x376819 == "0") {
        _0x34a145++;
      } else {
        if (_0x34a145 > 0) {
          _0x3756d9 += _0x161f42[0];
        }

        _0x34a145 = 0;
        _0x3756d9 += _0x161f42[Number(_0x376819)] + _0x2b0a2b[_0x5a4506];
      }

      if (_0x5a4506 == 0 && _0x34a145 < 4) {
        _0x3756d9 += _0x2429bf[_0x51a6ad];
        _0x34a145 = 0;
      }
    }

    _0x3756d9 += _0xbe74d5;
  }

  if (_0x428104 != "") {
    for (_0x3b7d17 = 0; _0x3b7d17 < _0x428104["length"]; _0x3b7d17++) {
      _0x376819 = _0x428104["substr"](_0x3b7d17, 1);

      if (_0x376819 != "0") {
        _0x3756d9 += _0x161f42[Number(_0x376819)] + _0x2ed94d[_0x3b7d17];
      }
    }
  }

  if (_0x3756d9 == "") {
    _0x3756d9 = _0x53e955 + _0xbe74d5;
  }

  if (_0x428104 == "" || _0x428104 == "00" || _0x428104 == "0") {
    _0x3756d9 += _0x12d3cc;
  }

  _0x3756d9 = _0x231039 + _0x3b5477 + _0x3756d9;
  return _0x3756d9;
}

function GetHwxxHtml(_0x84d81a, _0x31fbd0, _0x57dc46) {
  if (_0x84d81a == "") {
    return "";
  }

  var _0x49191f = _0x84d81a["split"]("\u2584");

  if (_0x49191f["length"] > 1) {
    _0x84d81a = _0x49191f[0];
  }

  var _0xe73ea3 = _0x84d81a["split"]("\u2261");

  var _0x3c9941;

  var _0x58a254 = "";
  var _0x1dfb88 = "";

  for (var _0x2ab7ff = 0; _0x2ab7ff < _0xe73ea3["length"]; _0x2ab7ff++) {
    _0x3c9941 = _0xe73ea3[_0x2ab7ff]["split"]("\u2588");
    _0x58a254 += "<tr>";

    for (var _0x4eca2a = 0; _0x4eca2a < 8; _0x4eca2a++) {
      if (_0x4eca2a != 7) {
        if (_0x4eca2a == 3 || _0x4eca2a == 4 || _0x4eca2a == 5 || _0x4eca2a == 6) {
          _0x58a254 += "<td class=\"align_right borderRight\"><span class=\"content_td_blue\">";
        } else {
          _0x58a254 += "<td class=\"align_left borderRight\"><span class=\"content_td_blue\">";
        }
      } else {
        _0x58a254 += "<td class=\"align_right\"><span class=\"content_td_blue\">";
      }

      if (_0x4eca2a == 6) {
        if (_0x31fbd0 == 10 || _0x31fbd0 == 14 || _0x31fbd0 == "04" && _0x57dc46 == "1") {
          if (_0x3c9941[8] && _0x3c9941[8] == "1") {
            _0x58a254 += "\u514D\u7A0E";
          } else {
            if (_0x3c9941[8] && _0x3c9941[8] == "2") {
              _0x58a254 += "\u4E0D\u5F81\u7A0E";
            } else {
              _0x58a254 += FormatSl(_0x3c9941[_0x4eca2a]);
            }
          }
        } else {
          _0x58a254 += FormatSl(_0x3c9941[_0x4eca2a]);
        }
      } else {
        if (_0x4eca2a == 7 && (_0x31fbd0 == 14 || _0x31fbd0 == "04" && _0x57dc46 == "1")) {
          if (_0x3c9941[8] == "1" || _0x3c9941[8] == "2" || _0x3c9941[8] == "3") {
            _0x58a254 += "***";
          } else {
            _0x58a254 += getzeroDot(GetJeToDot(_0x3c9941[_0x4eca2a]["trim"]()));
          }
        } else {
          if (_0x4eca2a == 4 || _0x4eca2a == 5 || _0x4eca2a == 7) {
            if (_0x4eca2a == 4 && _0x31fbd0 == 14) {
              _0x58a254 += _0x3c9941[_0x4eca2a]["trim"]();
            } else {
              _0x58a254 += getzeroDot(GetJeToDot(_0x3c9941[_0x4eca2a]["trim"]()));
            }
          } else {
            if (_0x4eca2a == 3) {
              if (_0x31fbd0 == 14) {
                _0x58a254 += _0x3c9941[_0x4eca2a]["trim"]();
              } else {
                _0x58a254 += getzeroDot(_0x3c9941[_0x4eca2a]);
              }
            } else {
              _0x58a254 += _0x3c9941[_0x4eca2a];
            }
          }
        }
      }

      _0x58a254 += "</span></td>";
    }

    _0x58a254 += "</tr>";
  }

  if (_0x49191f["length"] > 1) {
    sechw = _0x49191f[1];
    _0x58a254 += "<tr>";
    _0x58a254 += "<td class=\"align_center borderRight\"><span class=\"content_td_blue\"><button id=\"showmx\" class=\"blue_button\" style=\"position:relative!important;z-index:100\" onmousemove=\"this.className='green_button';\" onmouseout=\"this.className='blue_button';\" onclick=\"showmx('" + _0x31fbd0 + "','" + _0x57dc46 + "');\">\u67E5\u770B\u8D27\u7269\u660E\u7EC6\u6E05\u5355</button>";
    _0x58a254 += "</span></td>";

    for (var _0x4eca2a = 0; _0x4eca2a < 7; _0x4eca2a++) {
      if (_0x4eca2a == 6) {
        _0x58a254 += "<td class=\"align_center\"><span class=\"content_td_blue\">&nbsp;</span></td>";
      } else {
        _0x58a254 += "<td class=\"align_center borderRight\"><span class=\"content_td_blue\">&nbsp;</span></td>";
      }
    }

    _0x58a254 += "</tr>";
  }

  return _0x58a254;
}

function getzeroDot(_0x575434) {
  if (_0x575434["substring"](0, 2) == "-.") {
    _0x575434 = "-0." + _0x575434["substring"](2);
  } else {
    if (_0x575434["substring"](0, 1) == ".") {
      _0x575434 = "0." + _0x575434["substring"](1);
    }
  }

  return _0x575434;
}

function showmx(_0x490067, _0x1371ca) {
  var _0x5edeb1 = sechw["split"]("\u258E");

  var _0x5a3c1c;

  var _0x56ac96 = "";

  if (_0x5edeb1[0] != "") {
    for (var _0x351596 = 0; _0x351596 < _0x5edeb1["length"]; _0x351596++) {
      _0x5a3c1c = _0x5edeb1[_0x351596]["split"]("\u2588");
      _0x56ac96 += "<tr><td class=\"borderBottomTopNo content_td_blue\">" + (_0x351596 + 1) + "</td>";

      for (var _0x1ed931 = 0; _0x1ed931 < 8; _0x1ed931++) {
        if (_0x1ed931 == 0) {
          _0x56ac96 += "<td class=\"borderBottomTopNo align_left content_td_blue\">";
        } else {
          if (_0x1ed931 == 3 || _0x1ed931 == 4 || _0x1ed931 == 5 || _0x1ed931 == 6 || _0x1ed931 == 7) {
            _0x56ac96 += "<td class=\"borderBottomTopNo align_right content_td_blue\">";
          } else {
            _0x56ac96 += "<td class=\"borderBottomTopNo align_left content_td_blue\">";
          }
        }

        if (_0x1ed931 == 6) {
          if (_0x490067 == 14 || _0x490067 == "04" && _0x1371ca == "1" && _0x5a3c1c[8]) {
            if (_0x5a3c1c[8] == "1") {
              _0x56ac96 += "\u514D\u7A0E";
            } else {
              if (_0x5a3c1c[8] == "2") {
                _0x56ac96 += "\u4E0D\u5F81\u7A0E";
              } else {
                _0x56ac96 += FormatSl(_0x5a3c1c[_0x1ed931]);
              }
            }
          } else {
            _0x56ac96 += FormatSl(_0x5a3c1c[_0x1ed931]);
          }
        } else {
          if (_0x1ed931 == 3) {
            _0x56ac96 += getzeroDot(_0x5a3c1c[_0x1ed931]);
          } else {
            if (_0x1ed931 == 7 && (_0x490067 == 14 || _0x490067 == "04" && _0x1371ca == "1")) {
              if (_0x5a3c1c[8] == "1" || _0x5a3c1c[8] == "2" || _0x5a3c1c[8] == "3") {
                _0x56ac96 += "***";
              } else {
                _0x56ac96 += getzeroDot(GetJeToDot(_0x5a3c1c[_0x1ed931]["trim"]()));
              }
            } else {
              if (_0x1ed931 == 4 || _0x1ed931 == 5 || _0x1ed931 == 7) {
                _0x56ac96 += getzeroDot(GetJeToDot(_0x5a3c1c[_0x1ed931]["trim"]()));
              } else {
                _0x56ac96 += _0x5a3c1c[_0x1ed931];
              }
            }
          }
        }

        _0x56ac96 += "</td>";
      }

      _0x56ac96 += "</tr>";
    }
  }

  $("#tab_head_mx")["after"](_0x56ac96);
  sechw = "";

  if (_0x490067 == "01" || _0x490067 == "08") {
    $("#xiaoji1")["text"]($("#je_zp")["text"]());
    $("#xiaoji2")["text"]($("#se_zp")["text"]());
    $("#zongji1")["text"]($("#je_zp")["text"]());
    $("#zongji2")["text"]($("#se_zp")["text"]());
  } else {
    if (_0x490067 == "04") {
      $("#xiaoji1")["text"]($("#je_pp")["text"]());
      $("#xiaoji2")["text"]($("#se_pp")["text"]());
      $("#zongji1")["text"]($("#je_pp")["text"]());
      $("#zongji2")["text"]($("#se_pp")["text"]());
    }
  }

  popWin("hwmxqd");
  return;
}

function GetDzHwxxHtml(_0x549de9) {
  if (_0x549de9 == "") {
    return "";
  }

  var _0x46becf = _0x549de9["split"]("\u2261");

  var _0x5201ed;

  var _0x1aefb1 = "";

  for (var _0x2e642d = 0; _0x2e642d < _0x46becf["length"]; _0x2e642d++) {
    _0x5201ed = _0x46becf[_0x2e642d]["split"]("\u2588");
    _0x1aefb1 += "<tr>";

    for (var _0x316705 = 0; _0x316705 < 8; _0x316705++) {
      if (_0x316705 != 7) {
        if (_0x316705 == 3 || _0x316705 == 4 || _0x316705 == 5 || _0x316705 == 6) {
          _0x1aefb1 += "<td class=\"align_right borderRight\"><span class=\"content_td_blue\">";
        } else {
          _0x1aefb1 += "<td class=\"align_left borderRight\"><span class=\"content_td_blue\">";
        }
      } else {
        _0x1aefb1 += "<td class=\"align_right\"><span class=\"content_td_blue\">";
      }

      if (_0x316705 == 3) {
        _0x1aefb1 += getzeroDot(_0x5201ed[6]);
      } else {
        if (_0x316705 == 7) {
          if (_0x5201ed[8] && _0x5201ed[8] == "1") {
            _0x1aefb1 += "***";
          } else {
            if (_0x5201ed[8] && _0x5201ed[8] == "2") {
              _0x1aefb1 += "***";
            } else {
              if (_0x5201ed[8] && _0x5201ed[8] == "3") {
                _0x1aefb1 += "***";
              } else {
                _0x1aefb1 += getzeroDot(GetJeToDot(_0x5201ed[_0x316705]["trim"]()));
              }
            }
          }
        } else {
          if (_0x316705 == 4 || _0x316705 == 5) {
            _0x1aefb1 += getzeroDot(GetJeToDot(_0x5201ed[_0x316705]["trim"]()));
          } else {
            if (_0x316705 == 6) {
              if (_0x5201ed[8] && _0x5201ed[8] == "1") {
                _0x1aefb1 += "\u514D\u7A0E";
              } else {
                if (_0x5201ed[8] && _0x5201ed[8] == "2") {
                  _0x1aefb1 += "\u4E0D\u5F81\u7A0E";
                } else {
                  _0x1aefb1 += FormatSl(_0x5201ed[3]);
                }
              }
            } else {
              _0x1aefb1 += _0x5201ed[_0x316705];
            }
          }
        }
      }

      _0x1aefb1 += "</span></td>";
    }

    _0x1aefb1 += "</tr>";
  }

  return _0x1aefb1;
}

function popWin(_0x1319d0) {
  function _0x1376d9() {
    var _0x1319d0 = _0x648ea3 ? _0x648ea3 : document["body"],
        _0x43492b = _0x1319d0["scrollHeight"] > _0x1319d0["clientHeight"] ? _0x1319d0["scrollHeight"] : _0x1319d0["clientHeight"],
        _0x3750e2 = _0x1319d0["scrollWidth"] > _0x1319d0["clientWidth"] ? _0x1319d0["scrollWidth"] : _0x1319d0["clientWidth"];
  }

  var _0x577893,
      _0x3fa2f2,
      _0x140107 = 9000,
      _0x40d565 = false,
      _0x2f49ac = $("#" + _0x1319d0),
      _0x3654eb = _0x2f49ac["width"](),
      _0x4b4478 = _0x2f49ac["height"](),
      _0x43632b = _0x2f49ac["find"](".close"),
      _0x648ea3 = document["documentElement"],
      _0x214766 = ($(document)["width"]() - _0x2f49ac["width"]()) / 2;

  if (_0x4b4478 > 700) {
    _0x4b4478 = 700;
  }

  m = (_0x648ea3["clientHeight"] - _0x4b4478) / 2;

  if ($(document)["width"]() > 1100) {
    _0x214766 = (1100 - _0x2f49ac["width"]()) / 2;
  }

  if (_0x648ea3["clientHeight"] > 600) {
    m = (600 - _0x4b4478) / 2;
  }

  if (m < 0) {
    m = 0 - m;
  }

  $("#hwmxqd")["css"]({
    "left": _0x214766,
    "top": m,
    "display": "block",
    "z-index": 9001
  });
}

function FormatSl(_0x26877c) {
  _0x26877c = _0x26877c["trim"]();

  if (parseFloat(_0x26877c) == 0.5 || parseFloat(_0x26877c) == 0.005) {
    return "0.5%";
  }

  if (parseFloat(_0x26877c) < 1 && parseFloat(_0x26877c) > 0 && _0x26877c["substring"](0, 1) != ".") {
    _0x26877c = _0x26877c["substring"](1);
  }

  if (_0x26877c["substring"](0, 1) == ".") {
    _0x26877c = parseFloat("0" + _0x26877c) * 100;
    _0x26877c = _0x26877c["toString"]();
  }

  if (_0x26877c["length"] > 0) {
    if (_0x26877c["indexOf"](".") > 0 && _0x26877c["length"] > 2) {
      var _0x5634dd = _0x26877c["substring"](_0x26877c["length"] - 2, _0x26877c["length"]);

      if (".0" == _0x5634dd) {
        _0x26877c = _0x26877c["substring"](0, _0x26877c["indexOf"]("."));
      }
    }

    return _0x26877c + "%";
  } else {
    return "";
  }
}

function isMoney(_0x28ecee) {
  var _0x5931c2 = "^[-]?[0-9]*(.[0-9]{1,2})?$";

  var _0x4139c3 = new RegExp(_0x5931c2);

  if (_0x4139c3["test"](_0x28ecee)) {
    return true;
  } else {
    return false;
  }
}

function GetJeToDot(_0x173809) {
  if (!isMoney(_0x173809)) {
    return _0x173809;
  }

  if (typeof _0x173809 != "undefined" && _0x173809["trim"]() != "") {
    if (_0x173809["trim"]() == "-") {
      return _0x173809;
    }

    _0x173809 = _0x173809["trim"]() + "";

    if (_0x173809["substring"](0, 1) == ".") {
      _0x173809 = "0." + _0x173809["substring"](1, _0x173809["length"]);

      if (_0x173809["split"](".")[1]["length"] == 1) {
        _0x173809 += "0";
      }

      return _0x173809;
    }

    var _0x2ada7c = _0x173809["indexOf"](".");

    if (_0x2ada7c < 0) {
      _0x173809 += ".00";
    } else {
      if (_0x173809["split"](".")[1]["length"] == 1) {
        _0x173809 += "0";
      }
    }

    if (_0x173809["substring"](0, 2) == "-.") {
      _0x173809 = "-0." + _0x173809["substring"](2, _0x173809["length"]);
    }

    return _0x173809;
  } else {
    return _0x173809;
  }
}

function GethyzpHwxxHtml(_0x1a7e2b, _0x2c8ed7) {
  if (_0x1a7e2b == "") {
    return "";
  }

  var _0x1cb3d4 = _0x1a7e2b["split"]("\u2261");

  var _0x33e6dd;

  var _0x516f79 = "";

  for (var _0x2ea544 = _0x2c8ed7; _0x2ea544 < _0x1cb3d4["length"]; _0x2ea544 = _0x2ea544 + 2) {
    _0x33e6dd = _0x1cb3d4[_0x2ea544]["split"]("\u2588");
    _0x516f79 += "<tr>";

    for (var _0x2b43c8 = 0; _0x2b43c8 < 2; _0x2b43c8++) {
      if (_0x2b43c8 == 1) {
        _0x516f79 += "<td class=\"align_right\"><span class=\"content_td_blue\">";
      } else {
        _0x516f79 += "<td class=\"align_center\"><span class=\"content_td_blue\">";
      }

      if (_0x2b43c8 == 0) {
        _0x516f79 += _0x33e6dd[_0x2b43c8];
      } else {
        if (_0x2b43c8 == 1) {
          _0x516f79 += getzeroDot(GetJeToDot(_0x33e6dd[_0x2b43c8]["trim"]()));
        }
      }

      _0x516f79 += "</span></td>";
    }

    _0x516f79 += "<td >&nbsp;</td></tr>";
  }

  return _0x516f79;
}

function GetjsfpHwxxHtml(_0x6117f7) {
  if (_0x6117f7 == "") {
    return "";
  }

  var _0xbfa748 = _0x6117f7["split"]("\u2261");

  var _0x1ef1bb;

  var _0x26d83c = "";

  for (var _0x2630a8 = 0; _0x2630a8 < _0xbfa748["length"]; _0x2630a8++) {
    _0x1ef1bb = _0xbfa748[_0x2630a8]["split"]("\u2588");
    _0x26d83c += "<tr>";
    var _0x30dfc3 = "";

    for (var _0x139206 = 0; _0x139206 < 4; _0x139206++) {
      if (_0x139206 == 1) {
        _0x30dfc3 = "<td class=\"align_right\"><span class=\"content_td_blue\">" + getzeroDot(_0x1ef1bb[_0x139206]) + "</span></td>";
        continue;
      }

      if (_0x139206 == 2 || _0x139206 == 3) {
        _0x26d83c += "<td class=\"align_right\"><span class=\"content_td_blue\">";
      } else {
        _0x26d83c += "<td class=\"align_center\"><span class=\"content_td_blue\">";
      }

      if (_0x139206 == 2 || _0x139206 == 3) {
        if (_0x139206 == 2) {
          _0x26d83c += getzeroDot(GetJeToDot(_0x1ef1bb[_0x139206]["trim"]()));
          _0x26d83c += _0x30dfc3;
          _0x30dfc3 = "";
        } else {
          _0x26d83c += getzeroDot(GetJeToDot(_0x1ef1bb[_0x139206]["trim"]()));
        }
      } else {
        _0x26d83c += _0x1ef1bb[_0x139206];
      }

      _0x26d83c += "</span></td>";
    }

    _0x26d83c += "</tr>";
  }

  return _0x26d83c;
}

function isJson(_0x40dbd0) {
  var _0x427561 = typeof _0x40dbd0 == "object" && Object["prototype"]["toString"]["call"](_0x40dbd0)["toLowerCase"]() == "[object object]" && !_0x40dbd0["length"];

  return _0x427561;
}