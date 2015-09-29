* Superior Web Systems */ 
function displayHideBox(boxNumber) 
{ 
    if(document.getElementById("LightBox"+boxNumber).style.display=="none") { 
        document.getElementById("LightBox"+boxNumber).style.display="block"; 
        document.getElementById("grayBG").style.display="block"; 
    } else { 
        document.getElementById("LightBox"+boxNumber).style.display="none"; 
        document.getElementById("grayBG").style.display="none"; 
    } 
} 