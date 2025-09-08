import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { 
    Trash2, 
    Edit, 
    Eye, 
    MoreHorizontal, 
    Search, 
    Plus,
    Tag as TagIcon 
} from 'lucide-react';

interface Tag {
    id: number;
    name: string;
    slug: string;
    description?: string;
    color: string;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

interface TagsData {
    data: Tag[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    tags: TagsData;
    filters: {
        search?: string;
        per_page: number;
    };
}

export default function Index({ tags, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('admin.tags.index'), { 
            search: search || undefined,
            per_page: filters.per_page,
        }, { 
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (tag: Tag) => {
        if (confirm(`Are you sure you want to delete the tag "${tag.name}"?`)) {
            router.delete(route('admin.tags.destroy', tag.id), {
                preserveScroll: true,
            });
        }
    };

    const handlePageChange = (page: number) => {
        router.get(route('admin.tags.index'), {
            page,
            search: filters.search,
            per_page: filters.per_page,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AdminLayout>
            <Head title="Tags" />
            
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Tags</h1>
                        <p className="text-muted-foreground">
                            Manage blog tags and organize your content
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.tags.create')}>
                            <Plus className="h-4 w-4 mr-2" />
                            Create Tag
                        </Link>
                    </Button>
                </div>

                <div className="bg-card rounded-lg border">
                    <div className="p-6 border-b">
                        <form onSubmit={handleSearch} className="flex gap-4">
                            <div className="flex-1 relative">
                                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                                <Input
                                    type="text"
                                    placeholder="Search tags..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="pl-10"
                                />
                            </div>
                            <Button type="submit">Search</Button>
                        </form>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tag</TableHead>
                                <TableHead>Slug</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Sort Order</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead className="w-[70px]"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {tags.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={7} className="text-center py-8">
                                        <div className="flex flex-col items-center gap-2">
                                            <TagIcon className="h-8 w-8 text-muted-foreground" />
                                            <p className="text-muted-foreground">No tags found</p>
                                            <Button asChild variant="outline" size="sm">
                                                <Link href={route('admin.tags.create')}>
                                                    Create your first tag
                                                </Link>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ) : (
                                tags.data.map((tag) => (
                                    <TableRow key={tag.id}>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <div 
                                                    className="w-4 h-4 rounded-full border" 
                                                    style={{ backgroundColor: tag.color }}
                                                />
                                                <span className="font-medium">{tag.name}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <code className="text-sm bg-muted px-2 py-1 rounded">
                                                {tag.slug}
                                            </code>
                                        </TableCell>
                                        <TableCell>
                                            {tag.description ? (
                                                <span className="text-sm">
                                                    {tag.description.length > 50 
                                                        ? `${tag.description.substring(0, 50)}...` 
                                                        : tag.description
                                                    }
                                                </span>
                                            ) : (
                                                <span className="text-muted-foreground">—</span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={tag.is_active ? 'default' : 'secondary'}>
                                                {tag.is_active ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{tag.sort_order}</TableCell>
                                        <TableCell>
                                            {new Date(tag.created_at).toLocaleDateString()}
                                        </TableCell>
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="sm">
                                                        <MoreHorizontal className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem asChild>
                                                        <Link href={route('admin.tags.show', tag.id)}>
                                                            <Eye className="h-4 w-4 mr-2" />
                                                            View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={route('admin.tags.edit', tag.id)}>
                                                            <Edit className="h-4 w-4 mr-2" />
                                                            Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem 
                                                        onClick={() => handleDelete(tag)}
                                                        className="text-destructive"
                                                    >
                                                        <Trash2 className="h-4 w-4 mr-2" />
                                                        Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>

                    {tags.data.length > 0 && (
                        <div className="p-6 border-t flex items-center justify-between">
                            <div className="text-sm text-muted-foreground">
                                Showing {tags.from} to {tags.to} of {tags.total} tags
                            </div>
                            <div className="flex gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => handlePageChange(tags.current_page - 1)}
                                    disabled={tags.current_page === 1}
                                >
                                    Previous
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => handlePageChange(tags.current_page + 1)}
                                    disabled={tags.current_page === tags.last_page}
                                >
                                    Next
                                </Button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
