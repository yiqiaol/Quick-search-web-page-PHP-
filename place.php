<?php 
	function googleMapGeo($position){
		$url = "https://maps.googleapis.com/maps/api/geocode/json?";
		$api_key = "AIzaSyDX-cBo7DQYKHNauCOxDQ--7k0DsRJYmgk";
		$address = "address=".urlencode($position);
		$key = "&key=".$api_key;
		$jsonDoc = file_get_contents($url.$address.$key);
        $obj = json_decode($jsonDoc);
        $lat = $obj->results[0]->geometry->location->lat;
        $lng = $obj->results[0]->geometry->location->lng;
	    return $lat.",".$lng;
	}
	function googlePlace($position,$distance,$category,$keyword){
		$url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?";
		$location = "location=".urlencode($position);
		$radius = "&radius=".($distance*1600);
		$type = "&type=".urlencode($category);
		$keyword ="&keyword=".urlencode($keyword);
		$api_key= "AIzaSyChvP9u9AHVCfUWWInRX_yLhiOhmeHWiGk";
		$key = "&key=".$api_key;
		$jsonDoc = file_get_contents($url.$location.$radius.$type.$keyword.$key);
		$data = json_decode($jsonDoc);
		$data->loc = $position;
		$jsonDoc = json_encode($data);
		//insert into the json file
		echo $jsonDoc;
	}
	function placeDetail($placeId){
		$url = "https://maps.googleapis.com/maps/api/place/details/json?";
		$placeid = "placeid=".$placeId;
		$api_key = "AIzaSyDMuWlAfl6QXZIsCp_SDCkCfhmkXNxMfVE";
		$key = "&key=".$api_key;
		$jsonDoc = file_get_contents($url.$placeid.$key);
		//put image

		$obj = json_decode($jsonDoc);
		$photos = $obj->result->photos;
		$url = "https://maps.googleapis.com/maps/api/place/photo?";
		$maxwidth = "maxheight=750";
		$key = "&key=AIzaSyBo-Br80DpzIwo4xUzV7KoNMlOqiio9uWo";
		for($index = 0;$index<sizeof($photos) && $index<5;$index++){
			$binary = file_get_contents($url.$maxwidth."&photoreference=".$photos[$index]->photo_reference.$key);
            file_put_contents($index.".jpg", $binary);
		}
		echo $jsonDoc;
	}

	$keyword = $_GET["keyword"];
	$category = $_GET["category"];
	$distance = $_GET["distance"]; 
	$position = $_GET["position"];
	$selfPos = $_GET["selfPos"];
	$placeId = $_GET["placeId"]; 
	
	
?>
<?php	if(!empty($position)):
			if($selfPos == 1){
				googlePlace($position,$distance,$category,$keyword);
			}else{
			 	$position = googleMapGeo($position); 
		        googlePlace($position,$distance,$category,$keyword);
		    }
?>
<?php elseif (!empty($placeId)):
            placeDetail($placeId);   //return place detail parson file 
?>
<?php else: ?>
<!DOCTYPE html>
<html>
<head>
	<title>Travel and entertainment search</title>
	
	<script type="text/javascript">
		var noRecord;
		var newTable;
		var jsonDoc;
		var herePos;
		var selfPos;
		var reviewTable;
		var picTable;
		var div;
		var div2;
		var x,y;	
		var mapLat;
		
		function showDetail(jsonDoc){     //1.put image to server 2.create the reviewTable 
			//create review
		 	reviewTable = document.createElement("table");
		 	reviewTable.border="1";
            reviewTable.style.visibility="collapse";
            reviewTable.style.width = "100%";
            var review = jsonDoc.result.reviews;
            var tr,td,th,img; 
            if(typeof(review) == "undefined"){
            	tr = document.createElement("tr");
            	td = document.createElement("td");
            	td.innerHTML = "No Reviews Found";
            	tr.appendChild(td);
            	reviewTable.appendChild(tr);
            }else{
            	for(var k in review){
	            	if(k==5){
	            		break;
	            	}
	            	tr = document.createElement("tr");
	            	
	            	td = document.createElement("td");
	            	th = document.createElement("th");
	            	img = document.createElement("img");
	            	var potu = review[k].profile_photo_url;
	            	if(potu){
	            		img.src = review[k].profile_photo_url;
	            		img.style.width = 20+"px";
	            	}
	            	th.appendChild(img);
	            	th.innerHTML+=review[k].author_name;
	            	tr.appendChild(th);
	            	reviewTable.appendChild(tr);
	            	tr = document.createElement("tr");
	            	tr.style.height ="20px";
	            	td = document.createElement("td");
	            	td.innerHTML=review[k].text;
	            	tr.appendChild(td);
	            	reviewTable.appendChild(tr);
	            }
            }
            
            //create picture table
			picTable = document.createElement("table");
		 	picTable.border="1";
		 	picTable.style.width = "100%";
            picTable.style.visibility="collapse";
            photos = jsonDoc.result.photos;
            if(typeof(photos) == "undefined"){
            	tr = document.createElement("tr");
            	td = document.createElement("td");
            	td.innerHTML = "No Photos Found";
            	tr.appendChild(td);
            	picTable.appendChild(tr);            	
            }else{
            	for(k in photos){
					if(k == 5) break;
					tr = document.createElement("tr");
					td = document.createElement("td");
					pic  = document.createElement("img");

					pic.style.width = "100%";
					pic.src = k+".jpg?a="+photos[k].photo_reference;
					pic.onclick=function(){
						window.open(this.src);
					}
					td.appendChild(pic);
					tr.appendChild(td);
					picTable.appendChild(tr);
				} 	
            }
		}
		function placeDetail(placeId){
			document.getElementById("newArea").removeChild(newTable);
			var url = "place.php";
			var place = placeId;
			url+="?placeId="+place;
			var xmlhttp = new XMLHttpRequest();
            xmlhttp.open("Get",url, false); 
		 	xmlhttp.send();
		 	jsonDoc = JSON.parse(xmlhttp.responseText);
		 	showDetail(jsonDoc);   //create table

			div = document.createElement("div");
			div.id = "detail";
			bigName = document.createElement("h2");
			bigName.innerHTML = jsonDoc.result.name;

			p1 = document.createElement("h3");
			img1 =document.createElement("img");
			p1.innerHTML = "click to show reviews";
			img1.style.width = 20+"px";
			img1.src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
			div.appendChild(bigName);
			div.appendChild(p1);
			div.appendChild(img1);
			div.appendChild(reviewTable);

			p2 = document.createElement("h3");
			img2 =document.createElement("img");
			p2.innerHTML = "click to show photos";
			img2.style.width = 20+"px";
			img2.src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
			div.appendChild(p2);
			div.appendChild(img2);
			div.appendChild(picTable);

			img1.addEventListener("click",function(){
				p2.innerHTML = "click to show photos";
				picTable.style.visibility="collapse";
				img2.src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
				if(reviewTable.style.visibility=="visible"){
				    reviewTable.style.visibility="collapse";
					p1.innerHTML = "click to show reviews";
			 		img1.src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";	
				}else{
					reviewTable.style.visibility="visible";
					p1.innerHTML = "click to hide reviews";
				 	img1.src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";	
				}
			})
			
			img2.addEventListener("click",function(){
				reviewTable.style.visibility="collapse";
				p1.innerHTML = "click to show reviews";
			 	img1.src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
			    if(p2.innerHTML == "click to hide photos"){
			    	p2.innerHTML = "click to show photos";
				 	img2.src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
				 	picTable.style.visibility="collapse";
			    }else{
			    	picTable.style.visibility="visible";
			    	p2.innerHTML = "click to hide photos";
				 	img2.src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";
			    }

			})
			document.getElementById("newArea").appendChild(div);    
		}
		function mapParameter(location){
			mapLat = location.lat;
			mapLng = location.lng;
			var s = document.createElement("script");
			s.src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDjCwxl4ravtviNK5jIi9naKbIDVkwEYbw&callback=mapProduce";
			document.getElementById("newArea").appendChild(s);
		}

		function changeMode(mode){
			selectedMode = mode.id;
			calculateAndDisplayRoute();
			marker.setVisible(false);
		}
		function calculateAndDisplayRoute() {
			start = new google.maps.LatLng(lat,lng);
	        directionsService.route({
	          origin: start,  //?related to location(?here)
	          destination: myLatLng, 
	          travelMode: selectedMode
	        }, function(response, status) {
	          if (status == 'OK') {
	            directionsDisplay.setDirections(response);
	          } else {
	            window.alert('Directions request failed due to ' + status);
	          }
	        });
	      }
	   
		function mapProduce(){
	
			myLatLng = new google.maps.LatLng(mapLat,mapLng);//the ending point 
            directionsDisplay = new google.maps.DirectionsRenderer;
        	directionsService = new google.maps.DirectionsService;
			var map = new google.maps.Map(document.getElementById("mapPlace"),{
				zoom:15,
				center:myLatLng
			});
			directionsDisplay.setMap(map);
		    //choose the selected mode
			marker = new google.maps.Marker({
			 	position : myLatLng,
				map:map
			});
						
		}
		function showResult(jsonDoc){
			if(jsonDoc.status != "OK"){ //if there is no result
				document.getElementById("noRecord").style.display = "block";
			}else{                     //if has json result
				document.getElementById("noRecord").style.display = "none";
				if(document.getElementById("newArea")!=null){
					document.getElementById("newArea").innerHTML = "";
				}
				newTable = document.createElement("table");
				newTable.id = "newtable";
				newTable.border="1";
				var tr,th,td,img,name,address;
				count = jsonDoc.results;
				tr = document.createElement("tr");
				th = document.createElement("th");
				th.innerHTML = "Category";
				tr.appendChild(th);
				th = document.createElement("th");
				th.innerHTML = "Name";
				tr.appendChild(th);
				th = document.createElement("th");
				th.innerHTML = "Address";
				tr.appendChild(th);
				newTable.appendChild(tr);
				for(let k in count){
			        element = count[k];
					tr = document.createElement("tr");
					td = document.createElement("td");
					img = document.createElement("img");
					img.src = element.icon;
					img.style.width = "30px";
					img.style.height = "25px"; 
					td.appendChild(img);
					tr.appendChild(td);

					name = document.createElement("td");
					name.innerHTML = element.name;
					name.place_id = element.place_id;
					name.style.cursor = "pointer";
					name.onclick = function(){
						placeDetail(this.place_id);  
						
					}
					tr.appendChild(name);
					address = document.createElement("td");
                    p = document.createElement("h4");
					p.innerHTML = element.vicinity;
					p.location = element.geometry.location;
					address.appendChild(p);
					p.style.cursor = "pointer";
					p.addEventListener("click", function(){
						if(mapLat!=undefined && mapLat == this.location.lat){  
							this.parentElement.removeChild(m);
							mapLat = null;
						 }else{
							mapParameter(this.location);    //create map									
							this.parentElement.appendChild(m);
						 }	
					})
					tr.appendChild(address);
					newTable.appendChild(tr);
				}
				document.getElementById("newArea").appendChild(newTable);
			}
			
		}
		function getlocation(){
			m = document.getElementById("maparea");
			m.parentElement.removeChild(m);
			m.style.display="block";
			/*step1: get the current location*/
			var xmlhttp = new XMLHttpRequest();
			var url = "http://ip-api.com/json";
		 	xmlhttp.open("Get",url, false); //open,send,responseText are
		 	xmlhttp.send();
		 	jsonDoc = JSON.parse(xmlhttp.responseText);
		 	herePos = jsonDoc.lat+","+	jsonDoc.lon;	 
		 	document.myform.search.disabled = false;
		 	/*step2:ajax to submit the form information*/
			document.myform.addEventListener("submit", function(event){
				if(document.getElementById("newArea")!=null){
			 		document.getElementById("newArea").innerHTML = "";
			 	}
			 	if(document.getElementById("mapPlace")!=null){
			 		document.getElementById("mapPlace").innerHTML = "";
			 	}
				//dismiss the submit function 
				event.preventDefault();
				//get the form input
				var keyword = document.myform.Keyword.value;
				var category = document.myform.Category.value;
				var distance;
				var position;
				var url = "place.php";
				if(document.myform.distance.value == ""){
					distance = 10;
				}else{
				 	distance = document.myform.distance.value;
				}
				if(document.myform.startPoint[0].checked == true){
					selfPos = 1;
					position = herePos;
				}else{
					selfPos = 0;
					position = document.myform.location.value;
				}
                url+="?keyword="+keyword+"&category="+category+"&distance="+distance+"&position="+position+"&selfPos="+selfPos;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.open("Get",url, false); 
			 	xmlhttp.send();
			 	jsonDoc = JSON.parse(xmlhttp.responseText);

			 	//get location 
		 		var location = jsonDoc.loc;
			 	var result=location.split(",");
			 	lat = result[0];
				lng = result[1];
				showResult(jsonDoc);   
			});
			document.myform.addEventListener("reset", function(event){
				document.getElementById("newArea").innerHTML="";
				document.getElementById("noRecord").style.display = "none";
				document.myform.location.disabled = true;

			});
			document.getElementById("noRecord").style.display = "none";
		 	return true;
		}
		function here(value){
			if(value == "here"){
				document.myform.location.disabled = true;	
			}else{
				document.myform.location.disabled = false;	
			}
		}
	</script>
	
	<style type="text/css">
		fieldset{
			width: 50%;
			margin: 0px auto;
		}

	    #newArea{
			margin-top: 20px;
			width: 80%;
			text-align: center;
			margin-left:  auto;
			margin-right:  auto;
		}
		#newArea p{
			border: 1px;
			border-color: grey;
			border-style: solid;
			background-color: #e0e0E0;
			display:none;
		}
		#mapPlace {
			width: 300px;
		    height: 200px;  
		    position: absolute;	    
       }
       #mode {
       		width:80px;
       		height:100px;
       		position: absolute;
       		z-index: 99;

       }
       #mode li:hover{
       		background-color: #c0c0c0;
       }
       #mode ul{
       	height: 100%;
       	width: 100%;
       }
       #mode li{
       		width: 100%;
       		height: 33%;
       		background-color: #e0e0e0;
       		font-size: 12px;
       		text-align: center;
       }
       #detail{
       	text-align: center;
       }
       #newtable{
       	text-align: center;
       	width: 100%;
       }
       #maparea{
       	display: none;
       }
		table{
			border: solid;
			border-color: grey;
			border-collapse: collapse;
		}
		h1{
			margin-top: 0;
			margin-bottom: 0;
			text-align: center;
		}
		ul{
			margin: 0px;
			padding: 0px;
			overflow: hidden;
		}
		li{
			float:left;
			list-style-type: none;
		}
		p{
			margin-top: 5px;
			margin-bottom: 5px;

		}
		h4{
			margin: 0px;
			font-weight: normal;

		}
		#button{
			margin-left:12%;
			padding: 2%;
		}
		#noRecord{
			margin-top: 30px;
			text-align: center;
			margin-left: auto;
			margin-right: auto;
			width: 80%;
			border: 1px;
			border-color: grey;
			border-style: solid;
			background-color: #e0e0E0;
			display: none;
		}
	</style>
	
</head>
<body onload="getlocation()">
		<fieldset>
			<h1>Travel and Entertainment Search</h1>
			<hr>
			<form name="myform">
			<p>Keyword <input type="text" name="Keyword" required><br> 
			<p>Category <select name="Category">
				<option value="default">default</option>
				<option value="cafe">cafe</option>
				<option value="bakery">bakery</option>
				<option value="restaurant">restaurant</option>
				<option value="beauty_salon">beauty salon</option>
				<option value="casino">casino</option>
				<option value="movie_theater">movie theater</option>
				<option value="lodging">lodging</option>
				<option value="airport">airport</option>
				<option value="train_station">train station</option>
				<option value="subway_station">subway station</option>
				<option value="bus_station">bus station</option>
			</select><br>
			<div>
				<ul>
					<li>
						Distance(miles)<input type="text" name="distance" placeholder="10">from
					</li>
					<li>
						<input type="Radio" name="startPoint" value="here" checked="checked" onclick="here(this.value)">Here<br>
						<input type="Radio" name="startPoint" value="location" onclick="here(this)"><input type="text" name="location" placeholder="location" required="true" disabled="true" ><br>
					</li>
				</ul>
			</div>
			<div id="button">
				<input type="submit" name="search" value="search" disabled="disabled"> 
				<input type="reset" name="reset" value="clear" >
			</div>
		
	</form>
	</fieldset>
	    <div id="noRecord">No Records has been found</div>
		<div id="newArea" align="center"></div>
		<div id="maparea">
			<div id="mapPlace"></div>
			<div id="mode">
				<ul>
					<li id="WALKING" onclick="changeMode(this)">Walk there</li>
					<li id="BICYCLING" onclick="changeMode(this)">Bike there</li>
					<li id="DRIVING" onclick="changeMode(this)">Drive there</li>
				</ul>
			</div>
		</div>
		
			
</body>
</html>

<?php endif; ?>
