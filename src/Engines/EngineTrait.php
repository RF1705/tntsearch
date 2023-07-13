<?php
namespace TeamTNT\TNTSearch\Engines;

use TeamTNT\TNTSearch\Connectors\FileSystemConnector;
use TeamTNT\TNTSearch\Connectors\MySqlConnector;
use TeamTNT\TNTSearch\Connectors\PostgresConnector;
use TeamTNT\TNTSearch\Connectors\SQLiteConnector;
use TeamTNT\TNTSearch\Connectors\SqlServerConnector;
use TeamTNT\TNTSearch\Support\TokenizerInterface;

trait EngineTrait
{
    /**
     * @return string
     */
    public function getStoragePath()
    {
        return $this->config['storage'];
    }

    /**
     * @param array $config
     *
     * @return FileSystemConnector|MySqlConnector|PostgresConnector|SQLiteConnector|SqlServerConnector
     * @throws Exception
     */
    public function createConnector(array $config)
    {
        if (!isset($config['driver'])) {
            throw new Exception('A driver must be specified.');
        }

        switch ($config['driver']) {
            case 'mysql':
                return new MySqlConnector;
            case 'pgsql':
                return new PostgresConnector;
            case 'sqlite':
                return new SQLiteConnector;
            case 'sqlsrv':
                return new SqlServerConnector;
            case 'filesystem':
                return new FileSystemConnector;
        }
        throw new Exception("Unsupported driver [{$config['driver']}]");
    }

    public function query($query)
    {
        $this->query = $query;
    }

    public function disableOutput($value)
    {
        $this->disableOutput = $value;
    }

    public function setStemmer($stemmer)
    {
        $this->stemmer = $stemmer;
        $this->updateInfoTable('stemmer', get_class($stemmer));
    }

    public function getPrimaryKey()
    {
        if (isset($this->primaryKey)) {
            return $this->primaryKey;
        }
        return 'id';
    }

    public function stemText($text)
    {
        $stemmer = $this->getStemmer();
        $words   = $this->breakIntoTokens($text);
        $stems   = [];
        foreach ($words as $word) {
            $stems[] = $stemmer->stem($word);
        }
        return $stems;
    }

    public function getStemmer()
    {
        return $this->stemmer;
    }

    public function breakIntoTokens($text)
    {
        if ($this->decodeHTMLEntities) {
            $text = html_entity_decode($text);
        }
        return $this->tokenizer->tokenize($text, $this->stopWords);
    }

    public function info($text)
    {
        if (!$this->disableOutput) {
            echo $text . PHP_EOL;
        }
    }

    public function setInMemory($value)
    {
        $this->inMemory = $value;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @param TokenizerInterface $tokenizer
     */
    public function setTokenizer(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
        $this->updateInfoTable('tokenizer', get_class($tokenizer));
    }

}
