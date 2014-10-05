<?php

namespace Cocoders\Repository;

use Cocoders\Archive\Archive;
use Cocoders\Archive\ArchiveRepository;
use Cocoders\Archive\InMemoryArchive\InMemoryArchive;
use Cocoders\Connection;

class PgsqlArchiveRepository implements ArchiveRepository
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Archive $archive
     * @return void
     */
    public function add(Archive $archive)
    {
        $this->connection->beginTransaction();
        $this
            ->connection
            ->execute('INSERT INTO archive (name, is_uploaded) VALUES (:name, :isUploaded)', ['name' => $archive->getName(), 'isUploaded' => (integer) $archive->isUploaded()])
        ;
        foreach ($archive->getFiles() as $file) {
            $this
                ->connection
                ->execute('INSERT INTO archive_file (path, archive_name) VALUES (:path, :archiveName)', ['path' => $file->getPath(), 'archiveName' => $archive->getName()])
            ;
        }
        $this->connection->commit();
    }

    /**
     * @param string $name
     * @return Archive|null
     */
    public function findByName($name)
    {
        $archivesDetails = $this->connection->execute('SELECT a.*, f.* FROM archive a INNER JOIN archive_file f ON a.name = f.archive_name WHERE a.name = :name LIMIT 1', ['name' => $name]);

        $files = [];
        foreach ($archivesDetails as $archiveDetails) {
            if (isset($files[$archiveDetails['name']])) {
                $files[$archiveDetails['name']][] = $archiveDetails['path'];
            } else {
                $files[$archiveDetails['name']] = [$archiveDetails['path']];
            }
        }

        foreach ($files as $archiveName => $archiveFiles) {
            return new InMemoryArchive($archiveName, $archiveFiles);
        }
    }

    /**
     * @return Archive[]
     */
    public function findAll()
    {
        $archivesDetails = $this->connection->execute('SELECT a.*, f.* FROM archive a INNER JOIN archive_file f ON a.name = f.archive_name');

        $files = [];
        foreach ($archivesDetails as $archiveDetails) {
            if (isset($files[$archiveDetails['name']])) {
                $files[$archiveDetails['name']][] = $archiveDetails['path'];
            } else {
                $files[$archiveDetails['name']] = [$archiveDetails['path']];
            }
        }

        $archives = [];
        foreach ($files as $archiveName => $archiveFiles) {
            $archives[] = new InMemoryArchive($archiveName, $archiveFiles);
        }

        return $archives;
    }
}