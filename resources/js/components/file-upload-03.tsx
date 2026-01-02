'use client';

import { File, Trash } from 'lucide-react';
import React from 'react';
import { useDropzone } from 'react-dropzone';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';

export default function FileUpload({
    typeTitle = 'Картинки',
    maxSizeTitle = '1mb',
}: {
    typeTitle: string;
    maxSizeTitle: string;
}) {
    const [files, setFiles] = React.useState<File[]>([]);
    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop: (acceptedFiles) => setFiles(acceptedFiles),
    });

    const filesList = files.map((file) => (
        <li key={file.name} className="relative">
            <Card className="relative p-4">
                <div className="absolute top-1/2 right-4 -translate-y-1/2">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        aria-label="Remove file"
                        onClick={() =>
                            setFiles((prevFiles) =>
                                prevFiles.filter(
                                    (prevFile) => prevFile.name !== file.name,
                                ),
                            )
                        }
                    >
                        <Trash className="h-5 w-5" aria-hidden={true} />
                    </Button>
                </div>
                <CardContent className="flex items-center space-x-3 p-0">
                    <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-muted">
                        <File
                            className="h-5 w-5 text-foreground"
                            aria-hidden={true}
                        />
                    </span>
                    <div>
                        <p className="font-medium text-foreground">
                            {file.name}
                        </p>
                        <p className="mt-0.5 text-sm text-muted-foreground">
                            {file.size} bytes
                        </p>
                    </div>
                </CardContent>
            </Card>
        </li>
    ));
    return (
        <div className="flex w-full items-center justify-center p-10">
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-6">
                <div className="col-span-full">
                    <div
                        {...getRootProps()}
                        className={cn(
                            isDragActive
                                ? 'border-primary bg-primary/10 ring-2 ring-primary/20'
                                : 'border-border',
                            'mt-2 flex justify-center rounded-md border border-dashed px-6 py-20 transition-colors duration-200',
                        )}
                    >
                        <div>
                            <File
                                className="mx-auto h-12 w-12 text-muted-foreground/80"
                                aria-hidden={true}
                            />
                            <div className="mt-4 flex text-muted-foreground">
                                <p>Переташите файл(ы) или </p>
                                <label
                                    htmlFor="file"
                                    className="relative cursor-pointer rounded-sm pl-1 font-medium text-primary hover:text-primary/80 hover:underline hover:underline-offset-4"
                                >
                                    <span>выберите их</span>
                                    <input
                                        {...getInputProps()}
                                        id="file-upload-2"
                                        name="file-upload-2"
                                        type="file"
                                        className="sr-only"
                                    />
                                </label>
                                <p className="pl-1">для загрузки</p>
                            </div>
                        </div>
                    </div>
                    <p className="mt-2 text-sm leading-5 text-muted-foreground sm:flex sm:items-center sm:justify-between">
                        <span>{typeTitle}</span>
                        <span className="pl-1 sm:pl-0">{maxSizeTitle}</span>
                    </p>
                    {filesList.length > 0 && (
                        <>
                            <h4 className="mt-6 font-medium text-foreground">
                                Файл(ы)
                            </h4>
                            <ul role="list" className="mt-4 space-y-4">
                                {filesList}
                            </ul>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
