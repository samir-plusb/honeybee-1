if ('ProjectAssetInfo' === ctx.doc.type) 
{ 
    ctx._type = 'asset';
} 
else 
{ 
    ctx.ignore = true; 
}
