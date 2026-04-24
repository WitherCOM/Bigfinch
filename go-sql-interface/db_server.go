package main

import (
	"fmt"
	"log/slog"
	"strings"

	"github.com/ajitpratap0/GoSQLX/pkg/gosqlx"
	"github.com/ajitpratap0/GoSQLX/pkg/sql/ast"
	"github.com/go-mysql-org/go-mysql/mysql"
	"github.com/go-mysql-org/go-mysql/server"
)

var (
	defaultSchema = "bigfinch"
	allowedTables = []string{"transactions", "categories"}
)

type BigfinchAuthHandler struct {
	db *BigfinchDb
}

func NewBigfinchAuthHandler(db *BigfinchDb) BigfinchAuthHandler {
	return BigfinchAuthHandler{
		db: db,
	}
}

// GetCredential implements [server.AuthenticationHandler].
func (b BigfinchAuthHandler) GetCredential(email string) (credential server.Credential, found bool, err error) {
	slog.Debug(fmt.Sprintf("setting user to: %s", email))
	return server.Credential{
		Passwords:      b.db.GetValidKeysForUser(email),
		AuthPluginName: mysql.AUTH_NATIVE_PASSWORD,
	}, true, nil
}

// OnAuthFailure implements [server.AuthenticationHandler].
func (b BigfinchAuthHandler) OnAuthFailure(conn *server.Conn, err error) {
	slog.Debug(fmt.Sprintf("auth failed for %s, reason: %s", conn.GetUser(), err.Error()))
}

// OnAuthSuccess implements [server.AuthenticationHandler].
func (b BigfinchAuthHandler) OnAuthSuccess(conn *server.Conn) error {
	slog.Debug(fmt.Sprintf("auth successful for %s", conn.GetUser()))
	return nil
}

type BigfinchHandler struct {
	db *BigfinchDb
}

func NewBigfinchHandler(db *BigfinchDb) BigfinchHandler {
	return BigfinchHandler{db: db}
}

func (b BigfinchHandler) HandleFieldList(table string, fieldWildcard string) ([]*mysql.Field, error) {
	slog.Debug(fmt.Sprintf("field list: table=%s wildcard=%s", table, fieldWildcard))

	return []*mysql.Field{}, nil
}

// HandleOtherCommand implements [server.Handler].
func (b BigfinchHandler) HandleOtherCommand(cmd byte, data []byte) error {
	slog.Debug(fmt.Sprintf("other command: %d", cmd))
	return nil
}

// HandleQuery implements [server.Handler].
func (b BigfinchHandler) HandleQuery(query string) (*mysql.Result, error) {
	q := NormalizeMysqlQuery(query)
	lower := strings.ToLower(q)
	if strings.HasPrefix(lower, "set") || strings.HasPrefix(lower, "show") {
		return NewOKResult(), nil
	}
	if parsed, err := gosqlx.Parse(lower); err == nil && len(parsed.Statements) > 0 {
		if s, ok := parsed.Statements[0].(*ast.SelectStatement); ok {
			if s.TableName == "" { // system query
				return mysql.NewResult(NewSystemInfoResultSet(ColumnsToStringList(s.Columns))), nil
			} else if s.TableName == "information_schema.tables" {
				return GetTableInformationSchemaResult(ColumnsToStringList(s.Columns)), nil
			} else if s.TableName == "information_schema.schemata" {
				return GetSchemataInformationSchemaResult(ColumnsToStringList(s.Columns)), nil
			} else if s.TableName == "information_schema.collations" {
				return GetCollationInformationSchemaResult(ColumnsToStringList(s.Columns)), nil
			} else if s.TableName == "information_schema.columns" {
				return GetColumnInformationSchemaResult(ColumnsToStringList(s.Columns)), nil
			}
		}
	} else if err != nil {
		slog.Warn(fmt.Sprintf("gosqlx parse: %v", err))
	}

	slog.Info(fmt.Sprintf("empty ok result for query: %s", q))
	return NewOKResult(), nil
}

// HandleStmtClose implements [server.Handler].
func (b BigfinchHandler) HandleStmtClose(context any) error {
	slog.Debug("HandleStmtClose")
	return nil
}

// HandleStmtExecute implements [server.Handler].
func (b BigfinchHandler) HandleStmtExecute(context any, query string, args []any) (*mysql.Result, error) {
	slog.Debug("HandleStmtExecute")
	return nil, nil
}

// HandleStmtPrepare implements [server.Handler].
func (b BigfinchHandler) HandleStmtPrepare(query string) (params int, columns int, context any, err error) {
	slog.Debug("HandleStmtPrepare")
	return 0, 0, nil, nil
}

// UseDB implements [server.Handler].
func (b BigfinchHandler) UseDB(dbName string) error {
	slog.Debug(fmt.Sprintf("use db: %s", dbName))

	return nil
}
