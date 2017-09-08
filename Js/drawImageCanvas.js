//Drawing Image to Canvas

function el(id){return document.getElementById(id);} // Get elem by ID

var canvasA  = el("canvasA-");
var contextA = canvasA.getContext("2d");

var canvasB  = el("canvasB-");
var contextB = canvasB.getContext("2d");

var base64Result;

function readImageA() {
        var FR= new FileReader();
        FR.onload = function(e) {
           var img = new Image();
           img.onload = function() {
             contextA.drawImage(img, 0, 0, 300, 180);
           };
		   //Image saved in img.src variable
           img.src = e.target.result;
        };  
		FR.readAsDataURL(this.files[0]);
}



function render(src){
	var image = new Image();
	image.onload = function(){
		contextA.drawImage(image, 0, 0, 300, 180);
	};
	image.src = src;
}

function loadImage(src){
	//	Create our FileReader and run the results through the render function.
	var reader = new FileReader();
	reader.onload = function(e){
		render(e.target.result);
	};
	reader.readAsDataURL(src);
}

//Drag and Drop A

canvasA.addEventListener("dragover", function(e){
		e.preventDefault();
	}, 
	false);
canvasA.addEventListener("drop", function(e){
		e.preventDefault();
		loadImage(e.dataTransfer.files[0]);
	}, 
	false);


function readImageB() {
    if ( this.files && this.files[0] ) {
        var FR= new FileReader();
        FR.onload = function(e) {
           var img = new Image();
           img.onload = function() {
             contextB.drawImage(img, 0, 0, 300, 180);
           };
           img.src = e.target.result;
        };       
        FR.readAsDataURL( this.files[0] );
    }
}

// Required for displaying images on canvas
el("uploadFilePivotal_A-").addEventListener("change", readImageA, false);
el("uploadFilePivotal_B-").addEventListener("change", readImageB, false);





