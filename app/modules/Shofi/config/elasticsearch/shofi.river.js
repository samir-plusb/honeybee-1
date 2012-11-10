if ('ShofiWorkflowItem' === ctx.doc.type) 
{ 
	ctx._type = 'shofi'; 
} 
else 
{ 
	ctx.ignore = true; 
}
