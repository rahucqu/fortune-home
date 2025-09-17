import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Eye, Mail, Phone, Plus, Search, Trash2, User } from 'lucide-react';
import { useState } from 'react';

interface AgentsIndexProps {
    agents: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            phone: string;
            license_number: string | null;
            bio: string | null;
            photo: string | null;
            specializations: string[] | null;
            is_active: boolean;
            properties_count: number;
            created_at: string;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search: string;
        status: string;
    };
}

export default function AgentsIndex({ agents, filters }: AgentsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Agents', href: '/admin/agents' },
    ];

    const handleSearch = () => {
        router.get('/admin/agents', { search });
    };

    const handleReset = () => {
        setSearch('');
        router.get('/admin/agents');
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Agents" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Agents</h1>
                        <p className="text-muted-foreground">
                            Manage real estate agents and their information
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/admin/agents/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Add Agent
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filter Agents</CardTitle>
                        <CardDescription>
                            Search for specific agents.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex space-x-2">
                            <Input
                                placeholder="Search agents..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                className="max-w-sm"
                            />
                            <Button onClick={handleSearch}>
                                <Search className="h-4 w-4 mr-2" />
                                Search
                            </Button>
                            <Button variant="outline" onClick={handleReset}>
                                Reset
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Agents ({agents.total})</CardTitle>
                        <CardDescription>
                            A list of all agents in your system.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Agent</TableHead>
                                        <TableHead>Contact</TableHead>
                                        <TableHead>License</TableHead>
                                        <TableHead>Specializations</TableHead>
                                        <TableHead>Properties</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {agents.data.length > 0 ? (
                                        agents.data.map((agent) => (
                                            <TableRow key={agent.id}>
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="flex-shrink-0">
                                                            {agent.photo ? (
                                                                <img
                                                                    src={`/storage/${agent.photo}`}
                                                                    alt={agent.name}
                                                                    className="h-10 w-10 rounded-full object-cover"
                                                                />
                                                            ) : (
                                                                <div className="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                                    <User className="h-5 w-5 text-gray-500" />
                                                                </div>
                                                            )}
                                                        </div>
                                                        <div>
                                                            <div className="font-medium">{agent.name}</div>
                                                            {agent.bio && (
                                                                <div className="text-sm text-muted-foreground max-w-xs truncate">
                                                                    {agent.bio}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="space-y-1">
                                                        <div className="flex items-center space-x-1 text-sm">
                                                            <Mail className="h-3 w-3 text-muted-foreground" />
                                                            <span>{agent.email}</span>
                                                        </div>
                                                        <div className="flex items-center space-x-1 text-sm">
                                                            <Phone className="h-3 w-3 text-muted-foreground" />
                                                            <span>{agent.phone}</span>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="text-sm">
                                                        {agent.license_number || 'Not provided'}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {agent.specializations && agent.specializations.length > 0 ? (
                                                        <div className="flex flex-wrap gap-1">
                                                            {agent.specializations.slice(0, 2).map((spec, index) => (
                                                                <Badge key={index} variant="outline" className="text-xs">
                                                                    {spec}
                                                                </Badge>
                                                            ))}
                                                            {agent.specializations.length > 2 && (
                                                                <Badge variant="outline" className="text-xs">
                                                                    +{agent.specializations.length - 2} more
                                                                </Badge>
                                                            )}
                                                        </div>
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">None</span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary">
                                                        {agent.properties_count} properties
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        className={agent.is_active
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                                                        }
                                                    >
                                                        {agent.is_active ? 'Active' : 'Inactive'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="text-sm text-muted-foreground">
                                                        {new Date(agent.created_at).toLocaleDateString()}
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/agents/${agent.id}`}>
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/agents/${agent.id}/edit`}>
                                                                <Edit className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => {
                                                                if (agent.properties_count > 0) {
                                                                    alert('Cannot delete agent with associated properties.');
                                                                    return;
                                                                }
                                                                if (confirm('Are you sure you want to delete this agent?')) {
                                                                    router.delete(`/admin/agents/${agent.id}`);
                                                                }
                                                            }}
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={8} className="text-center py-8">
                                                <div className="space-y-2">
                                                    <User className="h-8 w-8 mx-auto text-muted-foreground" />
                                                    <p className="text-muted-foreground">No agents found.</p>
                                                    <Button asChild variant="outline">
                                                        <Link href="/admin/agents/create">
                                                            <Plus className="h-4 w-4 mr-2" />
                                                            Add First Agent
                                                        </Link>
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Pagination */}
                        {agents.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((agents.current_page - 1) * agents.per_page) + 1} to{' '}
                                    {Math.min(agents.current_page * agents.per_page, agents.total)} of{' '}
                                    {agents.total} agents
                                </div>
                                <div className="flex space-x-2">
                                    {agents.links.map((link, index) => (
                                        <Button
                                            key={index}
                                            variant={link.active ? "default" : "outline"}
                                            size="sm"
                                            disabled={!link.url}
                                            onClick={() => link.url && router.get(link.url)}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
