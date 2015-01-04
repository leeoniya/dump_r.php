(function(){
	/*--- all this for expand/collapse arrow size consistency ---*/
	function isUa(re) {return re.test(window.navigator.userAgent);}

	var ua = isUa(/Chrom[ei]/) ? "ch" : isUa(/Firefox\//) ? "ff" : isUa(/Safari/) ? "sf" : isUa(/Opera/) ? "op" : isUa(/; MSIE \d|Trident/) ? "ie" : "oth";

	var cfg = {
		ff: [10,8,null,null],
		ch: [10,10,null,12],
		sf: [10,8.5,null,null],
		op: [11,8.5,11,11],
		ie: [10,13.5,null,11]
	};

	var fn = "font-size: ",
		ln = "line-height: ",
		un = "pt",
		c = cfg[ua],
		fe = fn + c[0] + un,
		fc = fn + c[1] + un,
		le = c[2] ? ln + c[2] + un : "",
		lc = c[3] ? ln + c[3] + un : "",
		sheet = document.getElementById("dump_r").sheet;

	sheet.insertRule(".dump_r .expanded  > .excol {" + [fe,le].join(";") + "}", 5);
	sheet.insertRule(".dump_r .collapsed > .excol {" + [fc,lc].join(";") + "}", 5);
	/*-----------------------------------------------------------*/

	// expandable or collapsible tester
	var re = /\bexpanded\b|\bcollapsed\b/;

	function toggle(actn, node, lvls) {
		if (lvls === 0 || !re.test(node.className) || /\blimited\b/.test(node.className)) return;

		node.className = node.className.replace(actn ? /\bcollapsed\b/ : /\bexpanded\b/, actn ? "expanded" : "collapsed");

		for (var i in node.childNodes) {
			if (node.childNodes[i].nodeName !== "UL") continue;
			for (var j in node.childNodes[i].childNodes)
				toggle(actn, node.childNodes[i].childNodes[j], lvls - 1);
		}
	}

	function toggleHandler(e) {
		if (e.which != 1 || e.target.className.indexOf("excol") == -1) return;

		var node = e.target.parentNode,
			actn = node.className.indexOf("collapsed") !== -1 ? 1 : 0,
			lvls = e.shiftKey ? 1000 : 1;

		toggle(actn, node, lvls);

		// toggle all following siblings
		if (e.ctrlKey) {
			while (node.nextSibling) {
				node = node.nextSibling;
				toggle(actn, node, lvls);
			}
		}
	}

	function toggleAltVal(e) {
		if (e.which != 1 || e.target.className.indexOf("val") == -1) return;

		var val2 = e.target.getAttribute('data-val');

		if (val2 !== null) {
			val2 = val2.replace(/\\\\n/g, "\n");
			var oldVal = e.target.textContent.replace(/\n/g, "\\\\n");
			e.target.setAttribute("data-val", oldVal);
			e.target.textContent = val2;
		}

		e.preventDefault();
	}

	document.addEventListener("click", toggleHandler, false);

	document.addEventListener("dblclick", toggleAltVal, false);
})();