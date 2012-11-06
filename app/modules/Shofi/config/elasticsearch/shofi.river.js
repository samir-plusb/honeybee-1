if ('ShofiWorkflowItem' === ctx.doc.type) 
{ 
	ctx._type = 'shofi-place'; 
} 
else 
{ 
	ctx.ignore = true; 
}
