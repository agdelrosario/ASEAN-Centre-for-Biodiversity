function display(obj, name, target)
{
	if (obj == name) show(target);
	else hide(target);
}

function show(obj)
{
	obj1 = document.getElementById(obj);
	obj1.style.visibility = 'visible';
	obj1.style.display = 'block';
}

function hide(obj)
{
	obj1 = document.getElementById(obj);
	obj1.style.visibility = 'hidden';
	obj1.style.display = 'none';
}

function check(form, target, val)
{
	obj = document.forms[form].elements[target];
	limit = obj.length;
	
	obj.checked	= val;
	
	for (i = 0; i < limit; i++)
		obj[i].checked = val;
}