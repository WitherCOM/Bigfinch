package main

import (
	"log/slog"

	"github.com/go-mysql-org/go-mysql/mysql"
)

var (
	schemaName = "bigfinch"
	tables     = []string{"transactions", "categories"}
	columns    = []map[string]string{
		{
			"table":  "transactions",
			"column": "id",
			"type":   "varchar",
		},
		{
			"table":  "transactions",
			"column": "description",
			"type":   "varchar",
		},
		{
			"table":  "transactions",
			"column": "merchant",
			"type":   "varchar",
		},
		{
			"table":  "transactions",
			"column": "value",
			"type":   "double",
		},
		{
			"table":  "transactions",
			"column": "date",
			"type":   "datetime",
		},
		{
			"table":  "transactions",
			"column": "direction",
			"type":   "varchar",
		},
		{
			"table":  "transactions",
			"column": "category_id",
			"type":   "varchar",
		},
		{
			"table":  "transactions",
			"column": "tags",
			"type":   "text",
		},
		{
			"table":  "categories",
			"column": "id",
			"type":   "varchar",
		},
		{
			"table":  "categories",
			"column": "name",
			"type":   "varchar",
		},
	}
)

func GetTableInformationSchemaResult(infos []string) *mysql.Result {
	col := make([][]any, len(tables))
	for j, table := range tables {
		col[j] = make([]any, len(infos))
		for i, info := range infos {
			switch info {
			case "table_catalog":
				col[j][i] = "def"
			case "table_schema":
				col[j][i] = schemaName
			case "table_name":
				col[j][i] = table
			case "table_type":
				col[j][i] = "BASE TABLE"
			case "auto_increment":
				col[j][i] = nil
			default:
				col[j][i] = 1
			}
		}
	}

	if result, err := mysql.BuildSimpleTextResultset(infos, col); err == nil {
		return mysql.NewResult(result)
	} else {
		slog.Error(err.Error())
		return NewOKResult()
	}
}

func GetSchemataInformationSchemaResult(infos []string) *mysql.Result {
	col := make([][]any, 1)
	col[0] = make([]any, len(infos))
	for i, info := range infos {
		switch info {
		case "catalog_name":
			col[0][i] = "def"
		case "schema_name":
			col[0][i] = schemaName
		case "default_character_set_name":
			col[0][i] = "utf8mb4"
		case "default_collation_name":
			col[0][i] = "utf8mb4_general_ci"
		default:
			col[0][i] = nil
		}
	}
	if result, err := mysql.BuildSimpleTextResultset(infos, col); err == nil {
		return mysql.NewResult(result)
	} else {
		slog.Error(err.Error())
		return NewOKResult()
	}
}

func GetCollationInformationSchemaResult(infos []string) *mysql.Result {
	col := make([][]any, 0)
	if result, err := mysql.BuildSimpleTextResultset(infos, col); err == nil {
		return mysql.NewResult(result)
	} else {
		slog.Error(err.Error())
		return NewOKResult()
	}
}

func GetColumnInformationSchemaResult(infos []string) *mysql.Result {
	col := make([][]any, len(columns))
	for j, column := range columns {
		col[j] = make([]any, len(infos))
		for i, info := range infos {
			switch info {
			case "table_catalog":
				col[0][i] = "def"
			case "table_schema":
				col[0][i] = schemaName
			case "table_name":
				col[0][i] = column["table"]
			case "column_name":
				col[0][i] = column["column"]
			case "data_type":
				col[0][i] = column["type"]
			case "column_type":
				col[0][i] = column["type"]
			default:
				col[0][i] = nil
			}
		}
	}
	if result, err := mysql.BuildSimpleTextResultset(infos, col); err == nil {
		return mysql.NewResult(result)
	} else {
		slog.Error(err.Error())
		return NewOKResult()
	}
}
