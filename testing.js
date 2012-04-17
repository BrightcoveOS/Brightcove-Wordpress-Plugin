	document.ready.onLoad()
	{
		nc = document.getElementById("content");
   	nc.addEventListener("blur", function(e) {console.log("select starts at ", this.selectStart)}, false);
	}

	