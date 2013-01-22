
var LSColumnsPP = function(){
	/*  add preprocessor as parent */
	this.parent = LSColumnsPreprocessor;
	this.parent();
	
	this.preprocess_string = function(value) {
		return value.substring(1,value.length-1).replace(/\\(.)/g, '$1');
	};
	
};

var LSColumnsFilterListVisitor = function(all_columns, all_db_columns){
	var column_exists = function(col_name) {
		/* Could have used indexOf if IE7 wasn't crappy... */
		var i;
		for( i=0; i<all_columns.length; i++ ) {
			if( all_columns[i] == col_name ) {
				return true;
			}
		}
		return false;
	};
	var db_column_exists = function(col_name) {
		if( all_db_columns[col_name] ) {
			return true;
		}
		return false;
	};
	
	this.custom_cols = {};
	this.custom_deps = [];
	
	// entry: definition := * column_list end
	this.visit_entry = function(column_list0, end1) {
	};
	
	// column_list_single: column_list := * column
	this.visit_column_list_single = function(column0) {
		if( column0.op == 'add' ) {
			return column0.cols;
		}
		return [];
	};
	
	// column_list_multi: column_list := * column_list comma column
	this.visit_column_list_multi = function(column_list0, column2) {
		var result = [];
		if( column2.op == 'add' ) {
			var tmpresult = column_list0.concat(column2.cols);
			/* Only save unique columns. If this wasn't IE7-compatible, this would have been a simple filter... */
			for(var i=0; i<tmpresult.length; i++) {
				var to_add = true;
				for(var j=0; to_add && j<result.length; j++) {
					if(result[j] == tmpresult[i]) {
						to_add = false;
					}
				}
				if( to_add ) {
					result.push(tmpresult[i]);
				}
			}
		}
		if( column2.op == 'sub' ) {
			/* If we didn't have to care about IE7, this would be a simple Array.filter:
				result = column_list0.filter(function(el,i,a){return column2.cols.indexOf(el) < 0;});
				*/
			for(var i=0; i<column_list0.length; i++) {
				var to_add = true;
				for(var j=0; to_add && j<column2.cols.length; j++) {
					if(column2.cols[j] == column_list0[i]) {
						to_add = false;
					}
				}
				if( to_add ) {
					result.push(column_list0[i]);
				}
			}
		}
		return result;
	};
	
	// column_all: column := * all
	this.visit_column_all = function() {
		return {
			op: 'add',
			cols: all_columns
		};
	};
	
	// column_default: column := * name
	this.visit_column_default = function(name0) {
		if( !column_exists(name0) ) {
			return {op: 'nop'};
		}
		return {
			op: 'add',
			cols: [name0]
		};
	};
	
	// column_disable: column := * minus name
	this.visit_column_disable = function(name1) {
		if( !column_exists(name1) ) {
			return {op: 'nop'};
		}
		return {
			op: 'sub',
			cols: [name1]
		};
	};

	// column_custom: column := * custom_name eq expr
	this.visit_column_custom = function(custom_name0, expr2) {
		this.custom_cols[custom_name0] = expr2;
		return {
			op: 'add',
			cols: [custom_name0]
		};
	};
	
	// expr_add: expr := * expr op_add expr2
	this.visit_expr_add = function(expr0, expr2) {
		return function(args) {
			return expr0(args) + expr2(args);
			};
	};
	
	// expr_var: expr2 := * name
	this.visit_expr_var = function(name0) {
		if( !db_column_exists(name0) ) {
			return function(args) {
				return "Unknown field " + name0;
			};
		}
		this.custom_deps.push(name0);
		var name = name0;
		return function(args) {
			if( args.obj[name] )
				return args.obj[name];
			return '';
			};
	};

	// expr_string: expr2 := * string
	this.visit_expr_string = function(string0) {
		return function(args) {
			return string0;
			};
	};
	
	this.accept = function(result) {
		return result;
	};
	
};


function lsfilter_list_table_desc(metadata, columndesc)
{
	this.metadata = metadata;
	this.vis_columns = [];
	this.col_renderers = {};
	this.db_columns = [];
	
	if (!listview_renderer_table[metadata.table]) return;
	
	var all_col_renderers = listview_renderer_table[metadata.table];
	var all_columns = [];
	for ( var col in all_col_renderers) {
		all_columns.push(col);
	}
	var all_db_columns = livestatus_structure[metadata.table];
	var custom_columns = {};
	
	if (columndesc) {
		// TODO: handling of column slection description

		var columns_line_visitor = new LSColumnsFilterListVisitor(all_columns, all_db_columns);
		var parser = new LSColumns(new LSColumnsPP(), columns_line_visitor);
		try {
			this.vis_columns = parser.parse(columndesc);
			custom_columns = columns_line_visitor.custom_cols;
			this.db_columns = this.db_columns.concat(columns_line_visitor.custom_deps);
		} catch(e) {
			console.log(parser);
			console.log(columndesc);
			console.log(e);
			console.log(e.stack);
		}
	}
	else {
		this.vis_columns = all_columns;
	}
	
	/* Add custom column renderers */
	for( var name in custom_columns ) {
		var content = custom_columns[name];
		/* Some ugly way to bind variables... there must be a better way? */
		this.col_renderers[name] = (function(in_content){return {
			"header": name,
			"depends": [],
			"sort": false,
			"cell": function(args)
			{
				return $('<td />').append($(in_content(args)));
			}
		};})(content);
	}
	
	for ( var i = 0; i < this.vis_columns.length; i++) {
		/* Fetch column renderers */
		var column_obj = this.col_renderers[this.vis_columns[i]];
		if( !column_obj ) {
			column_obj = all_col_renderers[this.vis_columns[i]];
			this.col_renderers[this.vis_columns[i]] = column_obj;
		}
		/* Fetch database column dependencies */
		for ( var j = 0; j < column_obj.depends.length; j++) {
			this.db_columns.push(column_obj.depends[j]);
		}
	}
	
	/* Build fetch sort columns method */
	this.sort_cols = function(vis_col)
	{
		var sort = this.col_renderers[vis_col].sort;
		if (sort) return sort;
		return [];
	}
}