package main

import (
	"log/slog"

	"github.com/go-mysql-org/go-mysql/mysql"
)

func NewOKResult() *mysql.Result {
	return mysql.NewResultReserveResultset(0)
}

func NewStringArrayResultSet(key string, values []string) *mysql.Resultset {
	col := make([][]any, len(values))
	for i, val := range values {
		col[i] = []any{val}
	}
	if result, err := mysql.BuildSimpleTextResultset([]string{key}, col); err == nil {
		return result
	} else {
		slog.Error(err.Error())
		return &mysql.Resultset{}
	}
}

func NewIntArrayResultSet(key string, values []int32) *mysql.Resultset {
	col := make([][]any, len(values))
	for i, val := range values {
		col[i] = []any{val}
	}
	if result, err := mysql.BuildSimpleTextResultset([]string{key}, col); err == nil {
		return result
	} else {
		slog.Error(err.Error())
		return &mysql.Resultset{}
	}
}

func NewSystemInfoResultSet(requestInfo []string) *mysql.Resultset {
	col := make([][]any, 1)
	col[0] = make([]any, len(requestInfo))
	for i, info := range requestInfo {
		switch info {
		case "auto_increment_increment":
			col[0][i] = 0
		case "character_set_client":
			col[0][i] = 0
		case "character_set_connection":
			col[0][i] = 0
		case "version":
			col[0][i] = "8.0.0"
		case "@@version_comment":
			col[0][i] = ""
		case "database":
			col[0][i] = "bigfinch"
		case "schema":
			col[0][i] = "bigfinch"
		case "user_name":
			col[0][i] = "bigfinch"
		}
	}
	if result, err := mysql.BuildSimpleTextResultset(requestInfo, col); err == nil {
		return result
	} else {
		slog.Error(err.Error())
		return &mysql.Resultset{}
	}
}

func NewTablesFromInformationSchema() *mysql.Resultset {
	col := make([][]any, 2)
	col[0] = []any{"transactions"}
	col[1] = []any{"categories"}
	if result, err := mysql.BuildSimpleTextResultset([]string{"table_name"}, col); err == nil {
		return result
	} else {
		slog.Error(err.Error())
		return &mysql.Resultset{}
	}
}

func NewColumnsFromInformationSchema(table string) *mysql.Resultset {
	col := make([][]any, 2)
	col[0] = []any{"id", "string"}
	col[1] = []any{"name", "string"}
	if result, err := mysql.BuildSimpleTextResultset([]string{"column_name", "column_type"}, col); err == nil {
		return result
	} else {
		slog.Error(err.Error())
		return &mysql.Resultset{}
	}
}
