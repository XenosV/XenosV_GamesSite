var visible = 0;

function NameForFile(name)
{
	name = name.replace(/&/gi, "_");
	return name.replace(/[":#*%/\\]/gi, "");
}

function UnescapingCharacters(name)
{
	return name.replace(/%39/gi, "'");
}

function InitVar(vis)
{
	visible = vis;
}

function GenerateGameView(platform, sort_id)
{
	var games = document.getElementsByClassName('GV');
	
	for (var i = 0; i < games.length; i++)
	{
		var data_scr = games[i].getAttribute('data-src');
		if (data_scr != 0)
		{
			var game_name = games[i].textContent;
			games[i].textContent = "";
			var data = JSON.parse(data_scr);
			//games[i].removeAttribute('data-src');
			
			if (data.cm <= 1 && visible == 0 && sort_id == 0)
				games[i].style.display = "none";
			
			// Cover
			var elm = document.createElement("a");
			elm.setAttribute("href", "index.php?visible=" + visible + "&id=" + data.id);
			var img = document.createElement("img");
			img.setAttribute("loading", "lazy");
			img.setAttribute("class", "GameViewImage");
			switch(data.st)
			{
				case 1:
					var img2 = document.createElement("img");
					img2.setAttribute("class", "GameViewImageDigital");
					img2.setAttribute("src", "files/img/cover_digital/cover_" + platform + ".png");
					elm.appendChild(img2);
					img.setAttribute("style", "height:205px;");
					img.setAttribute("src", "files/games/" + NameForFile(game_name) + "_" + data.id + "/cover.jpg");
					break;
					
				case 2:
					img.setAttribute("style", "margin-top:0px;margin-bottom:0px;");
					img.setAttribute("src", "files/games/" + NameForFile(game_name) + "_" + data.id + "/cover_" + platform + ".jpg");
					break;
					
				default:
					img.setAttribute("style", "margin-top:0px;margin-bottom:0px;");
					img.setAttribute("src", "files/games/" + NameForFile(game_name) + "_" + data.id + "/cover.jpg");
					break;
			}
			elm.appendChild(img);
			games[i].appendChild(elm);
			
			// Name
			elm = document.createElement("a");
			elm.setAttribute("href", "index.php?visible=" + visible + "&id=" + data.id);
			elm.setAttribute("class", "GameViewName");
			elm.textContent = game_name;
			games[i].appendChild(elm);
		
			// Platforms
			elm = document.createElement("div");
			elm.setAttribute("class", "GameViewPlatformsContainer");
			for (var j = 0; j < data.pl.length; j++)
			{
				var child_elm = document.createElement("a");
				child_elm.setAttribute("class", "GameViewPlatforms");
				child_elm.setAttribute("style", "background:" + data.pl[j].c);
				child_elm.setAttribute("href", "index.php?visible=" + visible + "&platform=" + data.pl[j].id);
				var p_text = document.createElement("p");
				p_text.textContent = data.pl[j].p;
				p_text.setAttribute("style", "margin:0");
				child_elm.appendChild(p_text);
				elm.appendChild(child_elm);
			}
			games[i].appendChild(elm);
			
			//Genres
			elm = document.createElement("p");
			elm.setAttribute("class", "GameViewGenres");
			elm.textContent = UnescapingCharacters(data.gn);
			games[i].appendChild(elm);
			
			// Complete, rating and year
			elm = document.createElement("div");
			elm.setAttribute("class", "GameViewSortContainer");
				var child_elm = document.createElement("img");
				child_elm.setAttribute("src", "files/img/completed/completed" + data.cm + ".png");
				child_elm.setAttribute("height", "20px");
				elm.appendChild(child_elm);
				var child_elm = document.createElement("img");
				child_elm.setAttribute("src", "files/img/rating/rating" + data.rt + ".png");
				child_elm.setAttribute("height", "20px");
				child_elm.setAttribute("style", "margin-left: 30px");
				elm.appendChild(child_elm);
				var child_elm = document.createElement("p");
				child_elm.setAttribute("class", "GameViewYear");
				child_elm.textContent = data.yr;
				elm.appendChild(child_elm);
			games[i].appendChild(elm);
		}
	}
}

function ShowSortPlatforms()
{
	var btn_show = document.getElementById('btnShow');
	if (btn_show.classList.contains('show'))
	{
		btn_show.classList.remove('show');
		btn_show.innerHTML = "";
	}
	else
	{
		btn_show.classList.add('show');
		GenerateSortButtonsPlatform();
	}
}

function ShowSortGenres()
{
	var btn_show = document.getElementById('btnShow');
	if (btn_show.classList.contains('show'))
	{
		btn_show.classList.remove('show');
		btn_show.innerHTML = "";
	}
	else
	{
		btn_show.classList.add('show');
		GenerateSortButtons('gn');
	}
}

window.onclick = function(e)
{
	if (!e.target.matches('.Btn') && !e.target.matches('.down'))
	{
		var btn_show = document.getElementById('btnShow');
		if (btn_show.classList.contains('show'))
		{
			btn_show.classList.remove('show');
			btn_show.innerHTML = "";
		}
	}
}

function GenerateSortButtonsPlatform()
{
	var buttons = document.getElementById('btnShow');
	var data_scr = buttons.getAttribute('data-src');
	var first_generation = -1;
	var elm = null;
	
	if (data_scr != 0)
	{
		var data = JSON.parse(data_scr);
		for (var i = 0; i < data.pl.length; i++)
		{
			if (data.pl[i].gn != null)
			{	
				if (first_generation != data.pl[i].gn)
				{
					if (elm != null)
						buttons.appendChild(elm);
					elm = document.createElement("div");
					elm.setAttribute("style", "width:100%");
					elm.setAttribute("class", "SortersContanerGeneration");
					
					var elm2 = document.createElement("p");
					elm2.textContent = "??????????????????: " + data.pl[i].gn;
					elm.appendChild(elm2);
					
					first_generation = data.pl[i].gn;
				}
				
				var elm2 = document.createElement("a");
				elm2.setAttribute("href", "index.php?visible=" + data.pl[i].v + "&platform=" + data.pl[i].p + "&genre=" + data.pl[i].g);
				elm2.setAttribute("class", "ButtonSort");
				elm2.textContent = UnescapingCharacters(data.pl[i].name);
				elm2.setAttribute("style", "background:" + data.pl[i].bg);	
				elm.appendChild(elm2);
			}
		}
		
		if (elm != null)
			buttons.appendChild(elm);
	}
}

function GenerateSortButtons(type)
{
	var buttons = document.getElementById('btnShow');
	var data_scr = buttons.getAttribute('data-src');
	
	if (data_scr != 0)
	{
		var data = JSON.parse(data_scr);
		for (var i = 0; i < data[type].length; i++)
		{
			elm = document.createElement("a");
			elm.setAttribute("href", "index.php?visible=" + data[type][i].v + "&platform=" + data[type][i].p + "&genre=" + data[type][i].g);
			elm.setAttribute("class", "ButtonSort");
			elm.textContent = UnescapingCharacters(data[type][i].name);
			if (data[type][i].bg != null)
				elm.setAttribute("style", "background:" + data[type][i].bg);

			buttons.appendChild(elm);
		}
	}
}