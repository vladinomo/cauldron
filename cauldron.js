window.onload = function() {
	mat1 = document.getElementById('mat1');
	mat1.onfocus = function() {
		setMaterialToMenu(0);
	};
	mat2 = document.getElementById('mat2');
	mat2.onfocus = function() {
		setMaterialToMenu(1);
	};
};

function setMaterialToMenu(menuno) {
	selectmat = new Array();
	selectmat[0] = document.actbox.mat1;
	selectmat[1] = document.actbox.mat2;
	
	for(i=0;i<5;i++){
		if(selectmat[menuno].options[i].value == selectmat[1-menuno].value){
			if(matnum[selectmat[1-menuno]] <= 1){
				selectmat[menuno].options[i].disabled = true;
			} else {
				selectmat[menuno].options[i].disabled = false;
			}
		} else {
			selectmat[menuno].options[i].disabled = false;
		}
	}
	
}

